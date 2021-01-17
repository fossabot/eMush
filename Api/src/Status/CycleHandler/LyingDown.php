<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LyingDown extends AbstractCycleHandler
{
    protected string $name = PlayerStatusEnum::LYING_DOWN;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime): void
    {
        if (!$object instanceof Status && $object->getName() !== PlayerStatusEnum::LYING_DOWN) {
            return;
        }

        $player = $object->getPlayer();

        $playerEvent = new PlayerEvent($player, $dateTime);
        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(1)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
        ;

        $playerEvent
            ->setModifier($actionModifier)
            ->setReason(PlayerStatusEnum::LYING_DOWN)
        ;

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }

    public function handleNewDay($object, Daedalus $daedalus, \DateTime $dateTime): void
    {
    }
}
