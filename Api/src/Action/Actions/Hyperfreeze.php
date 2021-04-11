<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Perishable;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Hyperfreeze extends AbstractAction
{
    protected string $name = ActionEnum::HYPERFREEZE;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::RATION, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Perishable(['groups' => ['visibility']]));
        $metadata->addConstraint(new Status(['status' => EquipmentStatusEnum::FROZEN, 'contain' => false, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        if ($parameter->getEquipment()->getName() === GameRationEnum::COOKED_RATION ||
            $parameter->getEquipment()->getName() === GameRationEnum::ALIEN_STEAK) {
            /** @var GameItem $newItem */
            $newItem = $this->gameEquipmentService
                ->createGameEquipmentFromName(GameRationEnum::STANDARD_RATION, $this->player->getDaedalus())
            ;
            $equipmentEvent = new EquipmentEvent($newItem, VisibilityEnum::HIDDEN);
            $equipmentEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

            /** @var \Mush\Status\Entity\Status $status */
            foreach ($parameter->getStatuses() as $status) {
                $newItem->addStatus($status);
                $this->statusService->persist($status);
            }

            $equipmentEvent = new EquipmentEvent($parameter, VisibilityEnum::HIDDEN);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

            $this->gameEquipmentService->persist($newItem);
        } else {
            $this->statusService->createCoreStatus(
                EquipmentStatusEnum::FROZEN,
                $parameter
            );

            $this->gameEquipmentService->persist($parameter);
        }

        $this->playerService->persist($this->player);

        return new Success();
    }
}
