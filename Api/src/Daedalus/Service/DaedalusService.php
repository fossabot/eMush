<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\DAaedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusService implements DaedalusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private DaedalusRepository $repository;
    private RoomServiceInterface $roomService;
    private CycleServiceInterface $cycleService;
    private GameEquipmentServiceInterface $equipmentService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        DaedalusRepository $repository,
        RoomServiceInterface $roomService,
        CycleServiceInterface $cycleService,
        GameEquipmentServiceInterface $equipmentService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->roomService = $roomService;
        $this->cycleService = $cycleService;
        $this->equipmentService = $equipmentService;
        $this->randomService = $randomService;
    }

    /**
     * @codeCoverageIgnore
     */
    public function persist(Daedalus $daedalus): Daedalus
    {
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();

        return $daedalus;
    }

    /**
     * @codeCoverageIgnore
     */
    public function findById(int $id): ?Daedalus
    {
        return $this->repository->find($id);
    }

    /**
     * @codeCoverageIgnore
     */
    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection
    {
        return new DaedalusCollection();
    }

    public function findAvailableDaedalus(): ?Daedalus
    {
        return  $this->repository->findAvailableDaedalus();
    }

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection
    {
        return $daedalus->getGameConfig()->getCharactersConfig()->filter(
            fn (CharacterConfig $characterConfig) => !$daedalus->getPlayers()->exists(
                fn (int $key, Player $player) => ($player->getPerson() === $characterConfig->getName()))
        );
    }

    public function createDaedalus(GameConfig $gameConfig): Daedalus
    {
        $daedalus = new Daedalus();

        $daedalusConfig = $gameConfig->getDaedalusConfig();

        $daedalus
            ->setGameConfig($gameConfig)
            ->setCycle($this->cycleService->getCycleFromDate(new \DateTime()))
            ->setOxygen($daedalusConfig->getInitOxygen())
            ->setFuel($daedalusConfig->getInitFuel())
            ->setHull($daedalusConfig->getInitHull())
            ->setShield($daedalusConfig->getInitShield())
        ;

        $this->persist($daedalus);

        /** @var RoomConfig $roomconfig */
        foreach ($daedalusConfig->getRoomConfigs() as $roomconfig) {
            $room = $this->roomService->createRoom($roomconfig, $daedalus);
            $daedalus->addRoom($room);
        }

        $randomItemPlaces = $daedalusConfig->getRandomItemPlace();
        if (null !== $randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $item = $daedalus
                    ->getGameConfig()
                    ->getItemsConfig()
                    ->filter(fn (ItemConfig $item) => $item->getName() === $itemName)
                    ->first()
                ;
                $item = $this->itemService->createGameItem($item, $daedalus);
                $roomName = $randomItemPlaces
                    ->getPlaces()[$this->randomService->random(0, count($randomItemPlaces->getPlaces()) - 1)]
                ;
                $room = $daedalus->getRooms()->filter(fn (Room $room) => $roomName === $room->getName())->first();
                $item->setRoom($room);
                $this->itemService->persist($item);
            }
        }

        $daedalusEvent = new DaedalusEvent($daedalus);
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::NEW_DAEDALUS);

        return $this->persist($daedalus);
    }
}
