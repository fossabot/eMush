<?php

namespace Mush\Status\CycleHandler;

use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEventInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LyingDown extends AbstractStatusCycleHandler
{
    protected string $name = PlayerStatusEnum::LYING_DOWN;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
        if ($status->getName() !== PlayerStatusEnum::LYING_DOWN || !$statusHolder instanceof Player) {
            return;
        }

        $playerModifierEvent = new PlayerModifierEventInterface(
            $statusHolder,
            1,
            PlayerStatusEnum::LYING_DOWN,
            $dateTime
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::ACTION_POINT_MODIFIER);
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
