<?php

namespace Mush\Tests\unit\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Repository\DaedalusInfoRepository;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Repository\LocalizationConfigRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class DaedalusServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var DaedalusRepository|Mockery\Mock */
    private DaedalusRepository $repository;
    /** @var CycleServiceInterface|Mockery\Mock */
    private CycleServiceInterface $cycleService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var LocalizationConfigRepository|Mockery\Mock */
    private LocalizationConfigRepository $localizationConfigRepository;
    /** @var DaedalusInfoRepository|Mockery\Mock */
    private DaedalusInfoRepository $daedalusInfoRepository;
    /** @var DaedalusRepository|Mockery\Mock */
    private DaedalusRepository $daedalusRepository;

    private DaedalusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->repository = \Mockery::mock(DaedalusRepository::class);
        $this->cycleService = \Mockery::mock(CycleServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->localizationConfigRepository = \Mockery::mock(LocalizationConfigRepository::class);
        $this->daedalusInfoRepository = \Mockery::mock(DaedalusInfoRepository::class);
        $this->daedalusRepository = \Mockery::mock(DaedalusRepository::class);

        $this->service = new DaedalusService(
            $this->entityManager,
            $this->eventService,
            $this->repository,
            $this->cycleService,
            $this->randomService,
            $this->localizationConfigRepository,
            $this->daedalusInfoRepository,
            $this->daedalusRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreateDaedalus()
    {
        $roomConfig = new PlaceConfig();

        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();

        $item = new ItemConfig();
        $item->setEquipmentName('item');

        $randomItem = new RandomItemPlaces();
        $randomItem
            ->setItems([$item->getEquipmentName()])
            ->setPlaces([RoomEnum::LABORATORY])
        ;

        $daedalusConfig
            ->setInitShield(1)
            ->setInitFuel(2)
            ->setInitOxygen(3)
            ->setInitHull(4)
            ->setDailySporeNb(4)
            ->setPlaceConfigs(new ArrayCollection([$roomConfig]))
            ->setRandomItemPlaces($randomItem)
        ;
        $gameConfig
            ->setDaedalusConfig($daedalusConfig)
            ->setEquipmentsConfig(new ArrayCollection([$item]))
        ;

        $this->localizationConfigRepository
            ->shouldReceive('findByLanguage')
            ->with(LanguageEnum::FRENCH)
            ->once()
            ->andReturn(new LocalizationConfig())
        ;
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (DaedalusInitEvent $event) => (
                $event->getDaedalusConfig() === $daedalusConfig)
            )
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('persist')
            ->once();
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $daedalus = $this->service->createDaedalus($gameConfig, 'name', LanguageEnum::FRENCH);

        $this->assertInstanceOf(Daedalus::class, $daedalus);
        $this->assertEquals($daedalusConfig->getInitFuel(), $daedalus->getFuel());
        $this->assertEquals($daedalusConfig->getInitOxygen(), $daedalus->getOxygen());
        $this->assertEquals($daedalusConfig->getInitHull(), $daedalus->getHull());
        $this->assertEquals($daedalusConfig->getInitShield(), $daedalus->getShield());
        $this->assertEquals(0, $daedalus->getCycle());
        $this->assertEquals(GameStatusEnum::STANDBY, $daedalus->getGameStatus());
        $this->assertNull($daedalus->getCycleStartedAt());
        $this->assertEquals('name', $daedalus->getDaedalusInfo()->getName());
    }

    public function testStartDaedalus()
    {
        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setCyclePerGameDay(8)->setCycleLength(3 * 60);
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->cycleService
            ->shouldReceive('getInDayCycleFromDate')
            ->andReturn(2)
            ->once()
        ;
        $this->cycleService
            ->shouldReceive('getDaedalusStartingCycleDate')
            ->andReturn(new \DateTime('today midnight'))
            ->once()
        ;
        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $daedalus = $this->service->startDaedalus($daedalus);

        $this->assertEquals(GameStatusEnum::STARTING, $daedalus->getGameStatus());
        $this->assertEquals(new \DateTime('today midnight'), $daedalus->getCycleStartedAt());
        $this->assertEquals(2, $daedalus->getCycle());
    }

    public function testFindAvailableCharacterForDaedalus()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $characterConfigCollection = new ArrayCollection();
        $gameConfig->setCharactersConfig($characterConfigCollection);

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('character_1');
        $characterConfigCollection->add($characterConfig);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        $this->assertCount(1, $result);
        $this->assertEquals($characterConfig, $result->first());

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);
        $daedalus->addPlayer($player);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        $this->assertCount(0, $result);
    }

    public function testGetRandomAsphyxia()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

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
        $oxCapsuleConfig->setEquipmentName(ItemEnum::OXYGEN_CAPSULE);

        $oxCapsule1 = new GameItem($twoCapsulePlayer);
        $oxCapsule2 = new GameItem($twoCapsulePlayer);
        $oxCapsule3 = new GameItem($threeCapsulePlayer);
        $oxCapsule4 = new GameItem($threeCapsulePlayer);
        $oxCapsule5 = new GameItem($threeCapsulePlayer);

        $oxCapsule1
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;
        $oxCapsule2
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;
        $oxCapsule3
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;
        $oxCapsule4
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;
        $oxCapsule5
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
        ;

        // one player with no capsule
        $this->randomService->shouldReceive('getRandomPlayer')
            ->andReturn($noCapsulePlayer)
            ->once()
        ;
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->service->getRandomAsphyxia($daedalus, new \DateTime());

        $this->assertCount(2, $twoCapsulePlayer->getEquipments());
        $this->assertCount(3, $threeCapsulePlayer->getEquipments());

        // 2 players with capsules
        $this->eventService->shouldReceive('callEvent')->once();
        $this->randomService->shouldReceive('getRandomPlayer')
            ->andReturn($twoCapsulePlayer)
            ->once()
        ;

        $result = $this->service->getRandomAsphyxia($daedalus, new \DateTime());

        $this->assertCount(2, $twoCapsulePlayer->getEquipments());
        $this->assertCount(3, $threeCapsulePlayer->getEquipments());
    }

    public function testSelectAlphaMush()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setNbMush(2)
        ;

        $gameConfig->setDaedalusConfig($daedalusConfig);

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $characterConfigCollection = new ArrayCollection();
        $gameConfig->setCharactersConfig($characterConfigCollection);

        $player1 = $this->createPlayer($daedalus, 'player1');
        $characterConfig1 = $player1->getPlayerInfo()->getCharacterConfig();
        $characterConfigCollection->add($characterConfig1);

        $player2 = $this->createPlayer($daedalus, 'player2');
        $characterConfig2 = $player2->getPlayerInfo()->getCharacterConfig();
        $characterConfigCollection->add($characterConfig2);

        $player3 = $this->createPlayer($daedalus, 'player3');
        $characterConfig3 = $player3->getPlayerInfo()->getCharacterConfig();
        $characterConfigCollection->add($characterConfig3);

        $imunizedPlayer = $this->createPlayer($daedalus, 'imunizedPlayer');

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::IMMUNIZED);
        $characterConfigImunized = $imunizedPlayer->getPlayerInfo()->getCharacterConfig();
        $characterConfigImunized->setInitStatuses(new ArrayCollection([$statusConfig]));
        $characterConfigCollection->add($characterConfigImunized);

        $this->randomService->shouldReceive('getRandomElementsFromProbaCollection')
            ->withArgs(fn ($probaCollection, $number) => (
                $probaCollection instanceof ProbaCollection
                && $probaCollection->toArray() === ['player1' => 1, 'player2' => 1, 'player3' => 1]
                && $number === 2
            ))
            ->andReturn(['player1', 'player3'])
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->twice();

        $result = $this->service->selectAlphaMush($daedalus, new \DateTime());
    }

    public function testChangeHull()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxHull(100)->setInitHull(10);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $time = new \DateTime('yesterday');

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (DaedalusEvent $daedalusEvent, $eventName) => ($daedalusEvent->getTime() === $time && $eventName === DaedalusEvent::FINISH_DAEDALUS))
            ->once()
        ;

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->service->changeVariable(DaedalusVariableEnum::HULL, $daedalus, -20, $time);

        $this->assertEquals(0, $daedalus->getHull());

        $daedalusConfig->setMaxHull(20);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $this->service->changeVariable(DaedalusVariableEnum::HULL, $daedalus, 100, $time);

        $this->assertEquals(20, $daedalus->getHull());
    }

    protected function createPlayer(Daedalus $daedalus, string $name): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName($name)->setInitStatuses(new ArrayCollection([]));

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
        ;

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
