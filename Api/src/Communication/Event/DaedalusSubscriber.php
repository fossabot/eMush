<?php

namespace Mush\Communication\Event;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;

    public function __construct(ChannelServiceInterface $channelService)
    {
        $this->channelService = $channelService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::NEW_DAEDALUS => 'onDaedalusNew',
        ];
    }

    public function onDaedalusNew(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->channelService->createPublicChannel($daedalus);
    }
}
