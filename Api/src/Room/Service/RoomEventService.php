<?php

namespace Mush\Room\Service;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEventEnum;
use Mush\Room\Event\RoomEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RoomEventService implements RoomEventServiceInterface
{
    private RandomServiceInterface $randomService;
    private DifficultyConfig $difficultyConfig;
    private EventDispatcherInterface $eventDispatcher;
    private DaedalusServiceInterface $daedalusService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    /**
     * RoomService constructor.
     */
    public function __construct(
        RandomServiceInterface $randomService,
        GameConfigServiceInterface $gameConfigService,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->randomService = $randomService;
        $this->difficultyConfig = $gameConfigService->getDifficultyConfig();
        $this->eventDispatcher = $eventDispatcher;
        $this->daedalusService = $daedalusService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function handleIncident(Room $room, \DateTime $date): Room
    {
        //Tremors
        if ($this->randomService->isSuccessfull($this->difficultyConfig->getTremorRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        //Electric Arcs
        if ($this->randomService->isSuccessfull($this->difficultyConfig->getElectricArcRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        //Fire
        $this->handleFire($room, $date);

        return $room;
    }

    public function handleFire(Room $room, \DateTime $date): Room
    {
        $fireStatus = $room->getStatusByName(StatusEnum::FIRE);
        if ($fireStatus && !$fireStatus instanceof ChargeStatus) {
            throw new \LogicException('Fire is not a ChargedStatus');
        }

        if ($fireStatus && $fireStatus->getCharge() === 0) {
            //there is already a fire in the room
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::FIRE);

        //a secondary fire already started in this room this cycle OR no fire
        } elseif ($this->randomService->isSuccessfull($this->difficultyConfig->getStartingFireRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::CYCLE_FIRE);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);

            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::FIRE);
        }

        return $room;
    }

    public function propagateFire(Room $room, \DateTime $date): Room
    {
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessfull($this->difficultyConfig->getPropagatingFireRate())) {
                $roomEvent = new RoomEvent($adjacentRoom, $date);
                $roomEvent->setReason(RoomEventEnum::PROPAGATING_FIRE);
                $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
            }
        }

        return $room;
    }

    public function fireDamage(Room $room, \DateTime $date): Room
    {
        foreach ($room->getPlayers() as $player) {
            $damage = $this->randomService->getSingleRandomElementFromProbaArray($this->difficultyConfig->getFirePlayerDamage());
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

        if ($this->randomService->isSuccessfull($this->difficultyConfig->getHullFireDamageRate())) {
            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($this->difficultyConfig->getFireHullDamage()));

            $room->getDaedalus()->addHull(-$damage);
            $this->daedalusService->persist($room->getDaedalus());
        }

        return $room;
    }
}
