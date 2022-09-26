<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\EquipmentReachable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Event\Service\EventServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Transplant extends AbstractAction
{
    protected string $name = ActionEnum::TRANSPLANT;

    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GearToolServiceInterface $gearToolService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->gearToolService = $gearToolService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new EquipmentReachable(['name' => ItemEnum::HYDROPOT, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        // @TODO fail transplant
        /** @var Fruit $fruitType */
        $fruitType = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT);

        /** @var GameItem $hydropot */
        $hydropot = $this->gearToolService->getEquipmentsOnReachByName($this->player, ItemEnum::HYDROPOT)->first();

        $newHolder = $hydropot->getPlace();

        $equipmentEvent = new EquipmentEvent(
            ItemEnum::HYDROPOT,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime());
        $equipmentEvent->setExistingEquipment($hydropot);
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $equipmentEvent = new EquipmentEvent(
            $parameter->getName(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime());
        $equipmentEvent->setExistingEquipment($parameter);
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $equipmentEvent = new EquipmentEvent(
            $fruitType->getPlantName(),
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        return new Success();
    }
}
