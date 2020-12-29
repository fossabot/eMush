<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RetrieveOxygen extends Action
{
    protected string $name = ActionEnum::RETRIEVE_OXYGEN;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!$equipment = $actionParameters->getEquipment()) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        $equipmentConfig = $this->gameItem->getEquipment();

        return $this->player->canReachEquipment($this->gameEquipment) &&
            $this->gameEquipment->getEquipment()->$equipmentConfig->getName()===EquipmentEnum::OXYGEN_TANK &&
            $this->gameEquipmentService->isOperational($this->gameEquipment) &&
            $this->player->canReachEquipment($this->gameEquipment) &&
            $this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $gameItem=$this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::OXYGEN_CAPSULE, $this->player->getDaedalus());

        $gameItem->setPlayer($this->player);


        $this->gameEquipmentService->persist($gameItem);
        
        $this->player->getDaedalus()->addOxygen(-1);


        $target = new Target($this->gameEquipment->getName(), 'items');

        return new Success(ActionLogEnum::RETRIEVE_OXYGEN, VisibilityEnum::COVERT, $target);
    }
}
