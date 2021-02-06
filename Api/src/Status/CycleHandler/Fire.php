<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Fire extends AbstractStatusCycleHandler
{
    protected string $name = StatusEnum::FIRE;

    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService
    ) {
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }

    public function handleNewCycle(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime, array $context = []): void
    {
        if (!$status instanceof ChargeStatus || $status->getName() !== StatusEnum::FIRE) {
            return;
        }

        if (!$statusHolder instanceof Place) {
            throw new \LogicException('Fire status does not have a room');
        }

        //If fire is active
        if ($status->getCharge() > 0) {
            $this->propagateFire($statusHolder, $dateTime);
            $this->fireDamage($statusHolder, $dateTime);
        }
    }

    private function propagateFire(Place $room, \DateTime $date): Place
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessful($difficultyConfig->getPropagatingFireRate())) {
                $roomEvent = new RoomEvent($adjacentRoom, $date);
                $roomEvent->setReason(RoomEventEnum::PROPAGATING_FIRE);
                $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
            }
        }

        return $room;
    }

    private function fireDamage(Place $room, \DateTime $date): Place
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        foreach ($room->getPlayers() as $player) {
            $damage = $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getFirePlayerDamage());
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(-$damage)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;

            $playerEvent = new PlayerEvent($player, $date);
            $playerEvent->setReason(EndCauseEnum::BURNT);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        foreach ($room->getEquipments() as $equipment) {
            $this->gameEquipmentService->handleBreakFire($equipment, $date);
        }

        if ($this->randomService->isSuccessful($difficultyConfig->getHullFireDamageRate())) {
            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getFireHullDamage()));

            $this->daedalusService->changeHull($room->getDaedalus(), -$damage);
            $this->daedalusService->persist($room->getDaedalus());
        }

        return $room;
    }

    public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
        return;
    }
}
