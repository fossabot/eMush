<?php

namespace Mush\Status\Listener;

use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::STARTING_FIRE => 'onStartingFire',
            RoomEvent::STOP_FIRE => 'onStopFire',
        ];
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $fireStatusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(StatusEnum::FIRE, $event->getPlace()->getDaedalus());
        $fireStatus = $this->statusService->createStatusFromConfig($fireStatusConfig, $room);

        $this->statusService->persist($fireStatus);
    }

    public function onStopFire(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if (($fireStatus = $room->getStatusByName(StatusEnum::FIRE)) === null) {
            throw new \LogicException('room should have a fire to stop');
        }

        $this->statusService->delete($fireStatus);
    }
}
