<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::DELETE_PLACE => 'onDeletePlace',
        ];
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken() &&
                !($equipment instanceof Door) &&
                !($equipment instanceof GameItem) &&
                $equipment->isBreakable()) {
                $statusEvent = new StatusEvent(
                    EquipmentStatusEnum::BROKEN,
                    $equipment,
                    RoomEvent::ELECTRIC_ARC,
                    $event->getTime()
                );
                $statusEvent->setVisibility(VisibilityEnum::PUBLIC);
                $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
            }
        }
    }

    public function onDeletePlace(RoomEvent $event): void
    {
        foreach ($event->getPlace()->getEquipments() as $equipment) {
            $equipmentEvent = new EquipmentEvent(
                $equipment,
                false,
                VisibilityEnum::HIDDEN,
                $event->getReason(),
                $event->getTime()
            );

            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DELETE);
        }
    }
}
