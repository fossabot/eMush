<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEventInterface::NEW_PLAYER => 'onNewPlayer',
            PlayerEventInterface::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onNewPlayer(PlayerEventInterface $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();

        if ($daedalus->getPlayers()->count() === $daedalus->getGameConfig()->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        } elseif ($daedalus->getPlayers()->count() === 1) {
            $startDaedalusEvent = new DaedalusEvent(
                $daedalus,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($startDaedalusEvent, DaedalusEvent::START_DAEDALUS);
        }
    }

    public function onDeathPlayer(PlayerEventInterface $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();

        if ($player->getDaedalus()->getPlayers()->getPlayerAlive()->isEmpty() &&
            !in_array($reason, [EndCauseEnum::SOL_RETURN, EndCauseEnum::EDEN, EndCauseEnum::SUPER_NOVA, EndCauseEnum::KILLED_BY_NERON]) &&
            $player->getDaedalus()->getGameStatus() !== GameStatusEnum::STARTING
        ) {
            $endDaedalusEvent = new DaedalusEvent(
                $player->getDaedalus(),
                EndCauseEnum::DAEDALUS_DESTROYED,
                $event->getTime()
            );

            $this->eventDispatcher->dispatch($endDaedalusEvent, DaedalusEvent::END_DAEDALUS);
        }
    }
}
