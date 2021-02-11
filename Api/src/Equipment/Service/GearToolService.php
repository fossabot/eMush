<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Error;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GearToolService implements GearToolServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getApplicableGears(Player $player, array $scopes, array $types, ?string $target = null): Collection
    {
        /** @var Collection $gears */
        $gears = new ArrayCollection();

        /** @var GameItem $item */
        foreach ($player->getItems() as $item) {
            /** @var Gear $gear */
            $gear = $item->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gear) {
                foreach ($gear->getModifiers() as $modifier) {
                    if (in_array($modifier->getScope(), $scopes) &&
                        ($target === null || $modifier->getTarget() === $target) &&
                        (count($types) || in_array($modifier->getTarget(), $types)) &&
                        in_array($modifier->getReach(), [ReachEnum::INVENTORY]) &&
                        !$item->isBroken() &&
                        $item->isCharged()
                    ) {
                        $gears->add($item);
                        break;
                    }
                }
            }
        }

        return $gears;
    }

    public function getEquipmentsOnReach(Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        switch ($reach) {
            case ReachEnum::INVENTORY:
                return $player->getItems();

            case ReachEnum::SHELVE_NOT_HIDDEN:
                return new ArrayCollection(array_merge(
                    $player->getItems()->toArray(),
                    array_filter($player->getPlace()->getEquipments()->toArray(),
                        function (GameEquipment $gameEquipment) use ($player) {
                            return ($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) === null ||
                            $hiddenStatus->getTarget() === $player;
                        }
                    )
                ));

            case $reach === ReachEnum::SHELVE:
                return new ArrayCollection(array_merge(
                    $player->getItems()->toArray(),
                    $player->getPlace()->getEquipments()->toArray()));
            default:
                $room = $player->getDaedalus()->getPlaceByName($reach);
                if ($room === null) {
                    throw new Error('Invalid reach');
                }

                return $room
                    ->getEquipments();
        }
    }

    public function getEquipmentsOnReachByName(Player $player, string $equipmentName, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        return $this->getEquipmentsOnReach($player, $reach)->filter(fn (GameEquipment $equipment) => $equipment->getName() === $equipmentName);
    }

    public function getActionsTools(Player $player, array $scopes, ?string $target = null): Collection
    {
        /** @var Collection $actions */
        $actions = new ArrayCollection();

        $tools = $this->getToolsOnReach($player);

        foreach ($tools as $tool) {
            /** @var Action $action */
            $actions = $tool->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getAction();

            foreach ($actions as $action) {
                if (in_array($action->getScope(), $scopes) &&
                    ($target === null || $action->getTarget() === $target)
                ) {
                    $actions->add($action);
                }
            }
        }

        return $actions;
    }

    public function getToolsOnReach(Player $player): Collection
    {
        $equipments = $this->getEquipmentsOnReach($player);

        return $equipments->filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL) !== null);
    }

    public function getUsedTool(Player $player, string $actionName): ?GameEquipment
    {
        /** @var Collection $tools */
        $tools = new ArrayCollection();

        foreach ($this->getToolsOnReach($player) as $tool) {
            /** @var Tool $toolMechanic */
            $toolMechanic = $tool->getEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL);

            if ($toolMechanic &&
                $toolMechanic->getActions()->filter(fn (Action $action) => $action->getName() === $actionName)->isEmpty()
            ) {
                if ($tool->getStatusByName(EquipmentStatusEnum::CHARGES) === null) {
                    return $tool;
                } elseif ($tool->isCharged()) {
                    $tools->add($tool);
                }
            }
        }

        if (!$tools->isEmpty()) {
            return $tools->first();
        }

        return null;
    }

    public function applyChargeCost(GameEquipment $equipment): void
    {
        $chargeStatus = $equipment->getStatusByName(EquipmentStatusEnum::CHARGES);

        if ($chargeStatus &&
            $chargeStatus instanceof ChargeStatus &&
            $chargeStatus->getCharge() > 0
        ) {
            $equipmentEvent = new EquipmentEvent($equipment, VisibilityEnum::HIDDEN);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::CONSUME_CHARGE);
        }
    }
}
