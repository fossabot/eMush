<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LieDown extends Action
{
    protected string $name = ActionEnum::LIE_DOWN;

    private GameEquipment $gameEquipment;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return $this->gameEquipment->getEquipment()->hasAction(ActionEnum::LIE_DOWN) &&
            !$this->gameEquipment->isbroken() &&
            !$this->gameEquipment->getStatusByName(PlayerStatusEnum::LYING_DOWN) &&
            !$this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN) &&
            $this->player->canReachEquipment($this->gameEquipment);
    }

    protected function applyEffects(): ActionResult
    {
        $lyingDownStatus = new Status();
        $lyingDownStatus
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PLAYER_PUBLIC)
            ->setPlayer($this->player)
            ->setGameEquipment($this->gameEquipment)
        ;
        $this->statusService->persist($lyingDownStatus);

        return new Success(ActionLogEnum::LIE_DOWN, VisibilityEnum::PUBLIC);
    }
}
