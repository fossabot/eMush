<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Disassemble extends AttemptAction
{
    protected string $name = ActionEnum::DISASSEMBLE;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // @TODO add validator on technician skill ?
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::REINFORCED,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DISMANTLE_REINFORCED,
        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $target */
        $target = $this->target;

        if ($result instanceof Success) {
            $this->disassemble($target);
        }
    }

    private function disassemble(GameEquipment $gameEquipment): void
    {
        $time = new \DateTime();

        // add the item produced by disassembling
        foreach ($gameEquipment->getEquipment()->getDismountedProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $product = $this->gameEquipmentService->createGameEquipmentFromName(
                    $productString,
                    $this->player,
                    $this->getAction()->getActionTags(),
                    new \DateTime(),
                    VisibilityEnum::HIDDEN
                );
            }
        }

        // remove the dismantled equipment
        $equipmentEvent = new InteractWithEquipmentEvent(
            $gameEquipment,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
