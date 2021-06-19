<?php

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => ['onEquipmentCreated', -100],
            EquipmentEvent::EQUIPMENT_BROKEN => ['onEquipmentBroken', 10],
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed', 10],
            EquipmentEvent::EQUIPMENT_TRANSFORM => ['onEquipmentTransform', -100],
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        if (!$player = $event->getPlayer()) {
            throw new \Error('Player should be provided');
        }

        $equipment = $event->getEquipment();

        if ($equipment instanceof GameItem && $player->getItems()->count() >= $this->getGameConfig($equipment)->getMaxItemInInventory()) {
            $this->roomLogService->createLog(
                LogEnum::OBJECT_FELT,
                $player->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                $player,
                $equipment,
                null,
                $event->getTime()
            );
        }
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        if ($event->getVisibility() !== VisibilityEnum::HIDDEN) {
            $equipment = $event->getEquipment();
            if ($equipment instanceof Door) {
                $rooms = $equipment->getRooms()->toArray();
            } else {
                $rooms = [$equipment->getCurrentPlace()];
            }

            foreach ($rooms as $room) {
                $this->roomLogService->createLog(
                    LogEnum::EQUIPMENT_BROKEN,
                    $room,
                    $event->getVisibility(),
                    'event_log',
                    null,
                    $equipment,
                    null,
                    $event->getTime()
                );
            }
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        if ($event->getVisibility() !== VisibilityEnum::HIDDEN) {
            $equipment = $event->getEquipment();
            $place = $equipment->getCurrentPlace();

            $this->roomLogService->createLog(
                LogEnum::EQUIPMENT_DESTROYED,
                $place,
                $event->getVisibility(),
                'event_log',
                null,
                $equipment,
                null,
                $event->getTime()
            );
        }
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $player = $event->getPlayer();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if (
            $newEquipment instanceof GameItem && $player !== null &&
            $newEquipment->getPlayer() === null
        ) {
            $this->roomLogService->createLog(
                LogEnum::OBJECT_FELT,
                $player->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                $player,
                $newEquipment,
                null,
                $event->getTime()
            );
        }
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
