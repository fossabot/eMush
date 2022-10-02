<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Event\AbstractQuantityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusModifierSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
    ) {
        $this->daedalusService = $daedalusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $event): void
    {
        if (!$event instanceof DaedalusVariableEvent) {
            return;
        }

        $daedalus = $event->getDaedalus();
        $date = $event->getTime();
        $change = $event->getQuantity();

        switch ($event->getModifiedVariable()) {
            case DaedalusVariableEnum::HULL:
                $this->daedalusService->changeHull($daedalus, $change, $date);

                return;
            case DaedalusVariableEnum::OXYGEN:
                $this->daedalusService->changeOxygenLevel($daedalus, $change);

                return;
            case DaedalusVariableEnum::FUEL:
                $this->daedalusService->changeFuelLevel($daedalus, $change);

                return;
        }
    }
}
