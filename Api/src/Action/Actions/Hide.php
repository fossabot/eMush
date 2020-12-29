<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hide extends Action
{
    protected string $name = ActionEnum::HIDE;

    private GameItem $gameItem;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->player = $player;
        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        /** @var ItemConfig $itemConfig */
        $itemConfig = $this->gameItem->getEquipment();

        //Check that the item is reachable
        return $this->gameItem->getStatusByName(EquipmentStatusEnum::HIDDEN) === null &&
            $itemConfig->isHideable() &&
            $this->player->canReachEquipment($this->gameItem)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $hiddenStatus = new Status();
        $hiddenStatus
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setVisibility(VisibilityEnum::EQUIPMENT_PRIVATE)
            ->setPlayer($this->player)
            ->setGameEquipment($this->gameItem)
        ;

        if ($this->gameItem->getPlayer()) {
            $this->gameItem->setPlayer(null);
            $this->gameItem->setRoom($this->player->getRoom());
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        return new Success(ActionLogEnum::HIDE_SUCCESS, VisibilityEnum::COVERT);
    }
}
