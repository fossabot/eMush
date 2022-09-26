<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Perishable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Hyperfreeze extends AbstractAction
{
    protected string $name = ActionEnum::HYPERFREEZE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Perishable(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::FROZEN, 'contain' => false, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        if (
            $parameter->getEquipment()->getName() === GameRationEnum::COOKED_RATION ||
            $parameter->getEquipment()->getName() === GameRationEnum::ALIEN_STEAK
        ) {
            $equipmentEvent = new EquipmentEvent(
                GameRationEnum::STANDARD_RATION,
                $this->player,
                VisibilityEnum::PUBLIC,
                $this->getActionName(),
                new \DateTime()
            );
            $equipmentEvent->setExistingEquipment($parameter);
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);
        } else {
            $statusEvent = new StatusEvent(EquipmentStatusEnum::FROZEN, $parameter, $this->getActionName(), new \DateTime());
            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
        }

        return new Success();
    }
}
