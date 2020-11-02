<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Book;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class ReadBook extends Action
{
    protected const NAME = ActionEnum::READ_BOOK;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService
    ) {
        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->actionCost = new ActionCost();
        $this->actionCost->setActionPointCost(4);
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
        return $this->item->getItem()->getItemType(ItemTypeEnum::BOOK) !== null &&
            $this->player->canReachItem($this->item)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Book $bookType */
        $bookType = $this->item->getItem()->getItemType(ItemTypeEnum::BOOK);
        $this->player->addSkill($bookType->getSkill());

        $this->item
            ->setRoom(null)
            ->setPlayer(null)
        ;

        $this->itemService->delete($this->item);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::READ_BOOK,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
