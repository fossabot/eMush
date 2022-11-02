<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Repository\DiseaseCausesConfigRepository;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    private EntityManagerInterface|Mockery\Mock $entityManager;

    private DiseaseCausesConfigRepository|Mockery\Mock $diseaseCausesConfigRepository;

    private DiseaseConfigRepository|Mockery\Mock $diseaseConfigRepository;

    private RandomServiceInterface|Mockery\Mock $randomService;

    private EventServiceInterface|Mockery\Mock $eventService;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->diseaseCausesConfigRepository = Mockery::mock(DiseaseCausesConfigRepository::class);
        $this->diseaseConfigRepository = Mockery::mock(DiseaseConfigRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->eventService = Mockery::mock(EventServiceInterface::class);

        $this->playerDiseaseService = new PlayerDiseaseService(
            $this->entityManager,
            $this->diseaseCausesConfigRepository,
            $this->diseaseConfigRepository,
            $this->randomService,
            $this->eventService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateDiseaseFromNameAndWithDiseaseConfigDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDelayMin(4)->setDelayLength(4);

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->with('name', $daedalus)
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this
            ->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDelayMin(), $diseaseConfig->getDelayMin() + $diseaseConfig->getDelayLength()])
            ->andReturn(4)
            ->once()
        ;
        $this->eventService->shouldReceive('callEvent')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, DiseaseCauseEnum::INCUBATING_END);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::INCUBATING, $disease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithArgumentsDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->with('name', $daedalus)
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([10, 15])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, 'cause', 10, 5);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::INCUBATING, $disease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithoutDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->with('name', $daedalus)
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDiseasePointMin(), $diseaseConfig->getDiseasePointMin() + $diseaseConfig->getDiseasePointLength()])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->twice();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, 'reason');

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $disease->getStatus());
    }

    public function testHandleDiseaseForCause()
    {
        $daedalus = new Daedalus();
        $daedalus->setGameConfig(new GameConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseName = 'name';

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases([$diseaseName => 1])
            ->setName(DiseaseCauseEnum::PERISHED_FOOD)
        ;

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName($diseaseName)
            ->setDelayMin(4)
            ->setDelayLength(4)
        ;

        $this->diseaseCausesConfigRepository
            ->shouldReceive('findCausesByDaedalus')
            ->with(DiseaseCauseEnum::PERISHED_FOOD, $daedalus)
            ->andReturn($diseaseCauseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->with([$diseaseName => 1])
            ->andReturn($diseaseName)
            ->once()
        ;

        $this->diseaseConfigRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->with($diseaseName, $daedalus)
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->andReturn(1)
            ->once()
        ;

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ])->once();

        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);
    }

    public function testHandleNewCycle()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(10)
        ;

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(9, $diseasePlayer->getDiseasePoint());
    }

    public function testHandleNewCycleSpontaneousCure()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(0, $diseasePlayer->getDiseasePoint());
    }

    public function testHandleNewCycleIncubatedDiseaseAppear()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(10, $diseasePlayer->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $diseasePlayer->getStatus());
    }

    public function testHandleNewCycleIncubatedDiseaseAppearAndOverrodeDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setOverride([InjuryEnum::BROKEN_SHOULDER]);
        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setName(InjuryEnum::BROKEN_SHOULDER);
        $diseasePlayer2 = new PlayerDisease();
        $diseasePlayer2
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseaseConfig($diseaseConfig2)
            ->setDiseasePoint(1)
        ;
        $player->addMedicalCondition($diseasePlayer2);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (DiseaseEvent $event) => (
                $event->getPlayerDisease() === $diseasePlayer) &&
                $event->getReasons()[0] === DiseaseCauseEnum::INCUBATING_END
            )
            ->once()
        ;

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (DiseaseEvent $event) => (
                $event->getPlayerDisease() === $diseasePlayer2) &&
                $event->getReasons()[0] === DiseaseCauseEnum::OVERRODE
            )
            ->once()
        ;
        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(10, $diseasePlayer->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $diseasePlayer->getStatus());
    }

    public function testHealDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setResistancePoint(0)
        ;

        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, 'reason', new \DateTime());
    }

    public function testTreatDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setResistancePoint(1)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, 'reason', new \DateTime());

        $this->assertEquals(0, $diseasePlayer->getResistancePoint());
    }
}
