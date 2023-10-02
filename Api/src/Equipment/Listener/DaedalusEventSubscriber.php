<?php

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EquipmentEffectServiceInterface $equipmentEffectService,
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        $this->equipmentEffectService = $equipmentEffectService;
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', 1000],
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $this->equipmentEffectService->removeAllEffects($event->getDaedalus());
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {   
        $daedalus = $event->getDaedalus();
        $patrolShips = new ArrayCollection();

        foreach (EquipmentEnum::getPatrolShips() as $patrolShip) {
            $patrolShip = $this->gameEquipmentService->findByNameAndDaedalus($patrolShip, $daedalus)->first();
            if ($patrolShip instanceof GameEquipment) {
                $patrolShips->add($patrolShip);
            }
        }

        $patrolShipsInSpaceBattle = $patrolShips->filter(fn (GameEquipment $patrolShip) => $patrolShip->isInSpaceBattle());

        foreach ($patrolShipsInSpaceBattle as $patrolShip) {
            $destroyEquipmentEvent = new InteractWithEquipmentEvent(
                equipment: $patrolShip,
                author: null,
                visibility: VisibilityEnum::HIDDEN,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($destroyEquipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }   
    }
}
