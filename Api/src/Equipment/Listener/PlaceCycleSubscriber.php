<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlaceCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlaceCycleEvent::PLACE_NEW_CYCLE => 'onNewCycle',
            PlaceCycleEvent::PLACE_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(PlaceCycleEvent $event): void
    {
        $place = $event->getPlace();

        foreach ($place->getEquipments() as $equipment) {
            $itemNewCycle = new EquipmentCycleEvent(
                $equipment,
                $place->getDaedalus(),
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }

    public function onNewDay(PlaceCycleEvent $event): void
    {
        $room = $event->getPlace();

        foreach ($room->getEquipments() as $equipment) {
            $equipmentNewDay = new EquipmentCycleEvent(
                $equipment,
                $room->getDaedalus(),
                $event->getReasons()[0],
                $event->getTime()
            );

            $this->eventService->callEvent($equipmentNewDay, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);
        }
    }
}
