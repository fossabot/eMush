<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);
    }

    public function isVisible(): bool
    {
        return parent::isVisible() && $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN) !== null;
    }

    protected function applyEffects(): ActionResult
    {
        if ($lyingDownStatus = $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            $this->player->removeStatus($lyingDownStatus);
        }

        return new Success();
    }
}
