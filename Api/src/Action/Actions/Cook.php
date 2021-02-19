<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Cook extends AbstractAction
{
    protected string $name = ActionEnum::COOK;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gearToolService = $gearToolService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function isVisible(): bool
    {
        return parent::isVisible() &&
            ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::STANDARD_RATION ||
            $this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN)) &&
            $this->gearToolService->getUsedTool($this->player, $this->action->getName()) !== null &&
            $this->player->canReachEquipment($this->gameEquipment);
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::STANDARD_RATION) {
            /** @var GameItem $newItem */
            $newItem = $this->gameEquipmentService
                ->createGameEquipmentFromName(GameRationEnum::COOKED_RATION, $this->player->getDaedalus())
            ;

            $equipmentEvent = new EquipmentEvent($newItem, VisibilityEnum::HIDDEN);
            $equipmentEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

            foreach ($this->gameEquipment->getStatuses() as $status) {
                $newItem->addStatus($status);
                $status->setGameEquipment($newItem);
                $this->statusService->persist($status);
            }

            $equipmentEvent = new EquipmentEvent($this->gameEquipment, VisibilityEnum::HIDDEN);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

            $this->gameEquipmentService->persist($newItem);
        } elseif ($frozenStatus = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN)) {
            $this->gameEquipment->removeStatus($frozenStatus);
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        $this->playerService->persist($this->player);

        return new Success();
    }
}
