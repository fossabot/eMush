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
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Transplant extends AbstractAction
{
    protected string $name = ActionEnum::TRANSPLANT;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        ActionServiceInterface $actionService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
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
        if ($this->gearToolService->getEquipmentsOnReachByName($this->player, ItemEnum::HYDROPOT)->isEmpty() ||
            !$this->player->canReachEquipment($this->gameEquipment) ||
            !$this->gameEquipment->getEquipment()->hasAction($this->name)
        ) {
            return false;
        }

        return parent::isVisible();
    }

    protected function applyEffects(): ActionResult
    {
        //@TODO fail transplant
        /** @var Fruit $fruitType */
        $fruitType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT);

        /** @var GameItem $hydropot */
        $hydropot = $this->gearToolService->getEquipmentsOnReachByName($this->player, ItemEnum::HYDROPOT)->first();

        $place = $hydropot->getPlace() ?? $hydropot->getPlayer();

        /** @var GameItem $plantEquipment */
        $plantEquipment = $this->gameEquipmentService
                    ->createGameEquipmentFromName($fruitType->getPlantName(), $this->player->getDaedalus());

        if ($place instanceof Player) {
            $plantEquipment->setPlayer($place);
        } else {
            $plantEquipment->setPlace($place);
        }

        $equipmentEvent = new EquipmentEvent($this->gameEquipment, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $equipmentEvent = new EquipmentEvent($hydropot, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->gameEquipmentService->persist($plantEquipment);

        $this->playerService->persist($this->player);

        $type = $this->gameEquipment instanceof GameItem ? 'items' : 'equipments';
        $target = new Target($plantEquipment->getName(), $type);

        return new Success($target);
    }
}
