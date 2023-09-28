<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsReported;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReportFire extends AbstractAction
{
    protected string $name = ActionEnum::REPORT_FIRE;

    protected function support(?LogParameterInterface $support, array $parameters): bool
    {
        return $support === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => StatusEnum::FIRE, 'target' => HasStatus::PLAYER_ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsReported(['groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $reportEvent = new ApplyEffectEvent(
            $this->player,
            $this->support,
            VisibilityEnum::PRIVATE,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($reportEvent, ApplyEffectEvent::REPORT_FIRE);
    }
}
