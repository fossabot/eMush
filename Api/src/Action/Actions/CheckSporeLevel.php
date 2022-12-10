<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus as StatusValidator;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CheckSporeLevel extends AbstractAction
{
    protected string $name = ActionEnum::CHECK_SPORE_LEVEL;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new StatusValidator([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $player = $this->player;

        if ($player->getStatusByName(PlayerStatusEnum::IMMUNIZED)) {
            $success = new Success();

            return $success->setQuantity(0);
        }

        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null) {
            throw new \Error('Player should have a spore status');
        }

        if ($player->isMush()) {
            $nbSpores = 0;
        } else {
            $nbSpores = $sporeStatus->getCharge();
        }

        $success = new Success();

        return $success->setQuantity($nbSpores);
    }

    protected function applyEffect(ActionResult $result): void
    {
    }
}
