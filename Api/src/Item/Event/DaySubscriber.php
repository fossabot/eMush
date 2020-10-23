<?php

namespace Mush\Item\Event;

use Mush\Game\Event\DayEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(RoomServiceInterface $roomService, EventDispatcherInterface $eventDispatcher)
    {
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($item = $event->getItem())) {
            return;
        }
    }
}
