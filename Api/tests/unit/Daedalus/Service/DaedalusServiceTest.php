<?php

namespace Mush\Test\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var DaedalusRepository | Mockery\Mock */
    private DaedalusRepository $repository;
    /** @var PlaceServiceInterface | Mockery\Mock */
    private PlaceServiceInterface $placeService;
    /** @var CycleServiceInterface | Mockery\Mock */
    private CycleServiceInterface $cycleService;
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    private DaedalusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->repository = Mockery::mock(DaedalusRepository::class);
        $this->placeService = Mockery::mock(PlaceServiceInterface::class);
        $this->cycleService = Mockery::mock(CycleServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);

        $this->service = new DaedalusService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->placeService,
            $this->cycleService,
            $this->gameEquipmentService,
            $this->randomService,
            $this->roomLogService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateDaedalus()
    {
        $roomConfig = new PlaceConfig();

        $gameConfig = new GameConfig();
        $gameConfig->setCyclePerGameDay(8)->setCycleLength(3 * 60);
        $daedalusConfig = new DaedalusConfig();

        $item = new ItemConfig();
        $item->setName('item');

        $randomItem = new RandomItemPlaces();
        $randomItem
            ->setItems([$item->getName()])
            ->setPlaces([RoomEnum::LABORATORY])
        ;

        $daedalusConfig
            ->setInitShield(1)
            ->setInitFuel(2)
            ->setInitOxygen(3)
            ->setInitHull(4)
            ->setDailySporeNb(4)
            ->setPlaceConfigs(new ArrayCollection([$roomConfig]))
            ->setRandomItemPlace($randomItem)
        ;
        $gameConfig
            ->setDaedalusConfig($daedalusConfig)
            ->setEquipmentsConfig(new ArrayCollection([$item]))
        ;

        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $this->placeService
            ->shouldReceive('createPlace')
            ->andReturn($room)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->andReturn(0)
            ->once()
        ;

        $this->cycleService
            ->shouldReceive('getInDayCycleFromDate')
            ->andReturn(1)
            ->once()
        ;

        $this->cycleService
            ->shouldReceive('getDaedalusStartingCycleDate')
            ->andReturn(new \DateTime('today midnight'))
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->times(3)
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->twice()
        ;

        $this->gameEquipmentService
            ->shouldReceive('createGameEquipment')
            ->andReturn(new GameItem())
            ->once()
        ;
        $this->gameEquipmentService
            ->shouldReceive('persist')
            ->once()
        ;

        $daedalus = $this->service->createDaedalus($gameConfig);

        $this->assertInstanceOf(Daedalus::class, $daedalus);
        $this->assertEquals($daedalusConfig->getInitFuel(), $daedalus->getFuel());
        $this->assertEquals($daedalusConfig->getInitOxygen(), $daedalus->getOxygen());
        $this->assertEquals($daedalusConfig->getInitHull(), $daedalus->getHull());
        $this->assertEquals($daedalusConfig->getInitShield(), $daedalus->getShield());
        $this->assertEquals(1, $daedalus->getCycle());
        $this->assertEquals(new \DateTime('today midnight'), $daedalus->getCycleStartedAt());
        $this->assertCount(1, $daedalus->getPlaces());
        $this->assertCount(0, $daedalus->getPlayers());
    }

    public function testFindAvailableCharacterForDaedalus()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();
        $gameConfig->setCyclePerGameDay(8)->setCycleLength(3 * 60);

        $daedalus->setGameConfig($gameConfig);

        $characterConfigCollection = new ArrayCollection();
        $gameConfig->setCharactersConfig($characterConfigCollection);

        $characterConfig = new CharacterConfig();
        $characterConfig->setName('character_1');
        $characterConfigCollection->add($characterConfig);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        $this->assertCount(1, $result);
        $this->assertEquals($characterConfig, $result->first());

        $player = new Player();
        $player->setCharacterConfig($characterConfig);
        $daedalus->addPlayer($player);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        $this->assertCount(0, $result);
    }

    public function testGetRandomAsphyxia()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(3);

        $daedalus->setGameConfig($gameConfig);

        $room1 = new Place();
        $room2 = new Place();
        $room3 = new Place();

        $noCapsulePlayer = $this->createPlayer($daedalus, 'noCapsule');
        $twoCapsulePlayer = $this->createPlayer($daedalus, 'twoCapsule');
        $threeCapsulePlayer = $this->createPlayer($daedalus, 'threeCapsule');

        $noCapsulePlayer->setPlace($room1);
        $twoCapsulePlayer->setPlace($room2);
        $threeCapsulePlayer->setPlace($room3);

        $oxCapsuleConfig = new ItemConfig();
        $oxCapsuleConfig->setName(ItemEnum::OXYGEN_CAPSULE);

        $oxCapsule1 = new GameItem();
        $oxCapsule2 = new GameItem();
        $oxCapsule3 = new GameItem();
        $oxCapsule4 = new GameItem();
        $oxCapsule5 = new GameItem();

        $oxCapsule1
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($twoCapsulePlayer)
        ;
        $oxCapsule2
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($twoCapsulePlayer)
        ;

        $oxCapsule3
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($threeCapsulePlayer)
        ;
        $oxCapsule4
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($threeCapsulePlayer)
        ;
        $oxCapsule5
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($threeCapsulePlayer)
        ;

        $this->randomService->shouldReceive('getRandomPlayer')
            ->andReturn($twoCapsulePlayer)
            ->once()
        ;
        $this->roomLogService->shouldReceive('createLog')->once();
        $this->randomService->shouldReceive('getRandomPlayer')
            ->andReturn($noCapsulePlayer)
            ->once()
        ;
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('delete')->with($oxCapsule1)->once();

        $result = $this->service->getRandomAsphyxia($daedalus);

        $this->assertCount(1, $twoCapsulePlayer->getItems());
        $this->assertCount(3, $threeCapsulePlayer->getItems());
    }

    public function testSelectAlphaMush()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();
        $gameConfig
            ->setMaxItemInInventory(3)
            ->setNbMush(2);

        $daedalus->setGameConfig($gameConfig);

        $characterConfigCollection = new ArrayCollection();
        $gameConfig->setCharactersConfig($characterConfigCollection);

        $player1 = $this->createPlayer($daedalus, 'player1');
        $characterConfig1 = $player1->getCharacterConfig();
        $characterConfigCollection->add($characterConfig1);

        $player2 = $this->createPlayer($daedalus, 'player2');
        $characterConfig2 = $player2->getCharacterConfig();
        $characterConfigCollection->add($characterConfig2);

        $player3 = $this->createPlayer($daedalus, 'player3');
        $characterConfig3 = $player3->getCharacterConfig();
        $characterConfigCollection->add($characterConfig3);

        $imunizedPlayer = $this->createPlayer($daedalus, 'imunizedPlayer');
        $characterConfigImunized = $imunizedPlayer->getCharacterConfig();
        $characterConfigImunized->setStatuses([PlayerStatusEnum::IMMUNIZED]);
        $characterConfigCollection->add($characterConfigImunized);

        $this->randomService->shouldReceive('getRandomElementsFromProbaArray')
            ->with(['player1' => 1, 'player2' => 1, 'player3' => 1, 'imunizedPlayer' => 0], 2)
            ->andReturn(['player1', 'player3'])
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->twice();

        $result = $this->service->selectAlphaMush($daedalus);
    }

    protected function createPlayer(Daedalus $daedalus, string $name): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setName($name);

        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setCharacterConfig($characterConfig)
        ;

        return $player;
    }
}
