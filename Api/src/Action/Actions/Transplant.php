<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Transplant extends Action
{
    protected string $name = ActionEnum::TRANSPLANT;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return $this->player->getReachableEquipmentsByName(ItemEnum::HYDROPOT)->count() > 0 &&
                    $this->player->canReachEquipment($this->gameEquipment) &&
                    $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT)
                    ;
    }

    protected function applyEffects(): ActionResult
    {
        $fruitType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT);

        $hydropot = $this->player->getReachableEquipmentsByName(ItemEnum::HYDROPOT)->first();
        $place = $hydropot->getRoom() ?? $hydropot->getPlayer();

        $plantEquipment = $this->gameEquipmentService
                    ->createGameEquipmentFromName($fruitType->getPlantName(), $this->player->getDaedalus());

        if ($place instanceof Player && $plantEquipment instanceof GameEquipment) {
            $plantEquipment->setPlayer($place);
        } else {
            $plantEquipment->setRoom($place);
        }

        $hydropot->removeLocation();
        $this->gameEquipment->removeLocation();
        $this->gameEquipmentService->delete($hydropot);
        $this->gameEquipmentService->delete($this->gameEquipment);

        $this->gameEquipmentService->persist($plantEquipment);

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::TRANSPLANT,
            $this->player->getRoom(),
            $this->player,
            $this->gameEquipment,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
