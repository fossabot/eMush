<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Game\Enum\StatusEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Item\Service\ItemEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Consume extends Action
{
    protected const NAME = ActionEnum::CONSUME;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private ItemEffectServiceInterface $itemServiceEffect;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        ItemEffectServiceInterface $itemServiceEffect
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->itemServiceEffect = $itemServiceEffect;
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
        return $this->item->getItem()->hasAction(ActionEnum::CONSUME) &&
            !$this->player->hasStatus(StatusEnum::FULL_STOMACH);
    }


    protected function applyEffects(): ActionResult
    {
        $rationType = $this->item->getItem()->getRationsType();

        if ($rationType === null) {
            throw new \Exception('Cannot consume this item');
        }

        $itemEffect = $this->itemServiceEffect->getConsumableEffect($rationType, $this->player->getDaedalus());
        $this->player
            ->addActionPoint($itemEffect->getActionPoint())
            ->addMovementPoint($itemEffect->getMovementPoint())
            ->addHealthPoint($itemEffect->getHealthPoint())
            ->addMoralPoint($itemEffect->getMoralPoint())
        ;
        $this->playerService->persist($this->player);

        // if no charges consume item
        $this->item->setPlayer(null);
        $this->item->setRoom(null);
        $this->gameItemService->delete($this->item);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::CONSUME,
            $this->player->getRoom(),
            $this->item,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
