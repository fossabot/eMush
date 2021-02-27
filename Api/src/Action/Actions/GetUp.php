<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class GetUp extends AbstractAction
{
    protected string $name = ActionEnum::GET_UP;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Status(['status' => PlayerStatusEnum::LYING_DOWN, 'target' => Status::PLAYER]));
    }

    protected function applyEffects(): ActionResult
    {
        if ($lyingDownStatus = $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            $this->player->removeStatus($lyingDownStatus);
        }

        return new Success();
    }
}
