<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlant extends Action
{
    protected string $name = ActionEnum::TREAT_PLANT;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(2);
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
        return $this->player->canReachEquipment($this->gameEquipment) &&
                    $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT) &&
                    $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)
                    ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($diseased = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)) {
            $this->gameEquipment->removeStatus($diseased);
            $this->gameEquipmentService->persist($this->gameEquipment);
            $this->statusService->delete($diseased);
        }

        return new Success(ActionLogEnum::TREAT_PLANT_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
