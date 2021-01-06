<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExpressCook extends AbstractAction
{
    protected string $name = ActionEnum::EXPRESS_COOK;

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

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
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
        return ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::STANDARD_RATION ||
                $this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN)) &&
            $this->player->canReachEquipment($this->gameEquipment) &&
            !$this->gameEquipmentService
                ->getOperationalEquipmentsByName(ToolItemEnum::MICROWAVE, $this->player, ReachEnum::SHELVE_NOT_HIDDEN)->isEmpty()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::STANDARD_RATION) {
            /** @var GameItem $newItem */
            $newItem = $this->gameEquipmentService->createGameEquipmentFromName(GameRationEnum::COOKED_RATION, $this->player->getDaedalus());
            $equipmentEvent = new EquipmentEvent($newItem);
            $equipmentEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

            foreach ($this->gameEquipment->getStatuses() as $status) {
                $newItem->addStatus($status);
                $status->setGameEquipment($newItem);
                $this->statusService->persist($status);
            }

            $this->gameEquipment->removeLocation();
            $this->gameEquipmentService->delete($this->gameEquipment);
            $this->gameEquipmentService->persist($newItem);
        } elseif ($frozenStatus = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN)) {
            $this->gameEquipment->removeStatus($frozenStatus);
            $this->statusService->delete($frozenStatus);
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        $chargeStatus = $this->gameEquipmentService->getOperationalEquipmentsByName(
            ToolItemEnum::MICROWAVE,
            $this->player,
            ReachEnum::SHELVE_NOT_HIDDEN
        )
            ->first()
            ->getStatusByName(EquipmentStatusEnum::CHARGES)
        ;

        $chargeStatus->addCharge(-1);

        $this->statusService->persist($chargeStatus);

        //@TODO add effect on the link with sol

        $this->playerService->persist($this->player);

        return new Success(ActionLogEnum::EXPRESS_COOK_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
