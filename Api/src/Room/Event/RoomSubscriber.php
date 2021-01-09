<?php

namespace Mush\Room\Event;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Enum\RoomEventEnum;
use Mush\Room\Service\RoomEventServiceInterface;
use Mush\Room\Service\RoomServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private RoomEventServiceInterface $roomEventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RoomServiceInterface $roomService,
        RoomEventServiceInterface $roomEventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->roomService = $roomService;
        $this->roomEventService = $roomEventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::STARTING_FIRE => 'onStartingFire',
            RoomEvent::FIRE => 'onFire',
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $room = $event->getRoom();
        foreach ($room->getPlayers() as $player) {
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta($this->randomService->random(1, 3))
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setReason(EndCauseEnum::INJURY);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        $this->roomLogService->createRoomLog(
            LogEnum::TREMOR,
            $room,
            VisibilityEnum::PUBLIC,
            $event->getTime()
        );
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getRoom();
        foreach ($room->getPlayers() as $player) {
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(3)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setReason(EndCauseEnum::ELECTROCUTED);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken() &&
                !($equipment instanceof Door) &&
                !($equipment instanceof GameItem) &&
                $equipment->getEquipment()->getBreakableRate() > 0) {
                $equipmentEvent = new EquipmentEvent($equipment, VisibilityEnum::PUBLIC, $event->getTime());
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        $this->roomLogService->createRoomLog(
            LogEnum::ELECTRIC_ARC,
            $room,
            VisibilityEnum::PUBLIC,
            $event->getTime()
        );
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $fireStatus = $this->statusService->createChargeRoomStatus(StatusEnum::FIRE,
                    $event->getRoom(),
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    VisibilityEnum::PUBLIC,
                    1);

        if ($event->getReason() === RoomEventEnum::CYCLE_FIRE) {
            $fireStatus->setCharge(0);
        }
    }

    public function onFire(RoomEvent $event): void
    {
        $room = $event->getRoom();
        foreach ($room->getPlayers() as $player) {
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(2)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setReason(EndCauseEnum::BURNT);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        foreach ($room->getEquipments() as $equipment) {
            $this->gameEquipmentService->handleBreakFire($equipment, $event->getTime());
        }
    }
}
