<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Consume extends AbstractAction
{
    protected string $name = ActionEnum::CONSUME;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Item;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FULL_STOMACH,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::CONSUME_FULL_BELLY,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        /** @var Item $parameter */
        $parameter = $this->parameter;
        $rationType = $parameter->getConfig()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Item $parameter */
        $parameter = $this->parameter;
        $rationType = $parameter->getConfig()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        $consumeEquipment = new ApplyEffectEvent(
            $this->player,
            $parameter,
            VisibilityEnum::PRIVATE,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($consumeEquipment, ApplyEffectEvent::CONSUME);
    }
}
