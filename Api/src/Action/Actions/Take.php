<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\StatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Take extends Action
{
    private Player $player;
    private GameItem $item;
    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! $item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
    }

    public function canExecute(): bool
    {
        return $this->player->getRoom()->getItems()->contains($this->item) &&
            $this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory() &&
            $this->item->getItem()->isTakeable()
            ;
    }

    protected function applyActionCost(): void
    {
        //No costs
    }

    protected function applyEffects(): ActionResult
    {
        $this->item->setRoom(null);
        $this->item->setPlayer($this->player);

        // add BURDENED status if item is heavy and player hasn't SOLID skill
        if ($this->item->getItem()->isHeavy() &&
            !in_array(SkillEnum::SOLID, $this->player->getSkills())
        ) {
            $this->player->getSkills()[] = StatusEnum::BURDENED;
        }

        $this->itemService->persist($this->item);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::TAKE,
            $this->player->getRoom(),
            $this->item,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
