<?php

namespace Mush\Unit\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\CycleHandler\PlantCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;

class PlantCycleHandlerTest extends TestCase
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface|Mockery\Mock $randomService;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface|Mockery\Mock $eventService;
    /** @var EquipmentEffectServiceInterface|Mockery\Mock */
    private EquipmentEffectServiceInterface|Mockery\Mock $equipmentEffectService;

    private PlantCycleHandler $plantCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = Mockery::mock(EventServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->equipmentEffectService = Mockery::mock(EquipmentEffectServiceInterface::class);

        $this->plantCycleHandler = new PlantCycleHandler(
            $this->eventService,
            $this->gameEquipmentService,
            $this->randomService,
            $this->equipmentEffectService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNewCycle()
    {
        $plant = new ItemConfig();

        $plantType = new Plant();
        $plant->setMechanics(new ArrayCollection([$plantType]));

        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once(); // Plant should not get disease

        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setPlantDiseaseRate(50);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $gamePlant = new GameItem();
        $gamePlant
            ->setEquipment($plant);

        $chargeStatusConfig = new ChargeStatusConfig();
        $chargeStatusConfig->setName(EquipmentStatusEnum::PLANT_YOUNG);

        $chargeStatus = new ChargeStatus($gamePlant, $chargeStatusConfig);
        $chargeStatus->setCharge(1);

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect)->once();

        $this->plantCycleHandler->handleNewCycle($gamePlant, new \DateTime());

        $this->assertFalse(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => EquipmentStatusEnum::PLANT_YOUNG === $status->getName())
                ->isEmpty()
        );
        $this->assertTrue(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => EquipmentStatusEnum::PLANT_DISEASED === $status->getName())
                ->isEmpty()
        );
    }

    public function testNewCycleGetDiseaseAndGrow()
    {
        $plant = new ItemConfig();

        $plantType = new Plant();
        $plant->setMechanics(new ArrayCollection([$plantType]));

        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setPlantDiseaseRate(50);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $gamePlant = new GameItem();
        $gamePlant
                ->setEquipment($plant);

        $chargeStatusConfig = new ChargeStatusConfig();
        $chargeStatusConfig->setName(EquipmentStatusEnum::PLANT_YOUNG);
        $chargeStatus = new ChargeStatus($gamePlant, $chargeStatusConfig);
        $chargeStatus->setCharge(1);

        // Plant get disease and grow
        $chargeStatus->setCharge(10);

        $gamePlant
                ->setEquipment($plant)
                ->setHolder(new Place());

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->eventService
                ->shouldReceive('callEvent')
                ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::PLANT_DISEASED && $event->getStatusHolder() === $gamePlant)
                ->once();

        $this->eventService
                ->shouldReceive('callEvent')
                ->withArgs(fn (AbstractGameEvent $event) => $event instanceof StatusEvent &&
                    $event->getStatusName() === EquipmentStatusEnum::PLANT_YOUNG &&
                    $event->getStatusHolder() === $gamePlant)
                ->once();

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(1, $gamePlant->getStatuses());
    }

    public function testNewCycleAlreadyDiseased()
    {
        $plant = new ItemConfig();

        $plantType = new Plant();
        $plant->setMechanics(new ArrayCollection([$plantType]));

        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setPlantDiseaseRate(50);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $gamePlant = new GameItem();
        $gamePlant
            ->setEquipment($plant);

        // Plant already diseased can't get disease
        $diseaseConfig = new StatusConfig();
        $diseaseConfig->setName(EquipmentStatusEnum::PLANT_DISEASED);
        $diseaseStatus = new Status($gamePlant, $diseaseConfig);

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(1, $gamePlant->getStatuses());
    }

    public function testNewDayPlantHealthy()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(10);
        $player = new Player();
        $player->setDaedalus($daedalus);
        $room = new Place();
        $room->addPlayer($player);
        $room->setDaedalus($daedalus);

        $time = new \DateTime();

        $newFruit = new ItemConfig();
        $newFruit->setName('fruit name');

        $gameFruit = new GameItem();
        $gameFruit->setEquipment($newFruit);

        $plant = new ItemConfig();
        $plant
            ->setName('plant name');
        $plantType = new Plant();
        $plantType->setFruit($newFruit);

        $plant->setMechanics(new ArrayCollection([$plantType]));

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);

        $gamePlant = new GameItem();
        $gamePlant
            ->setName('plant name')
            ->setEquipment($plant)
            ->setHolder($room);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);
        $this->gameEquipmentService->shouldReceive('persist');
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof StatusEvent &&
                $event->getStatusName() === EquipmentStatusEnum::PLANT_THIRSTY &&
                $event->getStatusHolder() === $gamePlant)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof DaedalusModifierEvent &&
                $event->getDaedalus() === $daedalus &&
                $event->getQuantity() === 10
            )->once();
        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof EquipmentEvent &&
                $event->getEquipment()->getName() === $newFruit->getName()
            )->once()
        ;
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        // Mature Plant, no problem
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, $time);

        $this->assertCount(1, $room->getEquipments());
    }

    public function testNewDayPlantThirsty()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(10);
        $player = new Player();
        $player->setDaedalus($daedalus);
        $room = new Place();
        $room->addPlayer($player);
        $room->setDaedalus($daedalus);

        $newFruit = new ItemConfig();
        $newFruit->setName('fruit name');

        $this->gameEquipmentService->shouldReceive('persist');

        $plant = new ItemConfig();
        $plant
            ->setName('plant name');
        $plantType = new Plant();
        $plantType->setFruit($newFruit);

        $plant->setMechanics(new ArrayCollection([$plantType]));

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10);
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $gamePlant = new GameItem();
        $gamePlant
            ->setName('plant name')
            ->setEquipment($plant)
            ->setHolder($room);

        $thirstyConfig = new StatusConfig();
        $thirstyConfig->setName(EquipmentStatusEnum::PLANT_THIRSTY);
        $status = new Status($gamePlant, $thirstyConfig);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof StatusEvent &&
                $event->getStatusName() === EquipmentStatusEnum::PLANT_DRY &&
                $event->getStatusHolder() === $gamePlant)
            ->once();

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof DaedalusModifierEvent &&
                $event->getDaedalus() === $daedalus &&
                $event->getQuantity() === 10)
            ->once();

        // Thirsty plant
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(1, $room->getEquipments());

        $this->gameEquipmentService->shouldReceive('createEquipment')->andReturn(new GameItem());
    }

    public function testNewDayPlantDry()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(10);
        $player = new Player();
        $player->setDaedalus($daedalus);
        $room = new Place();
        $room->addPlayer($player);
        $room->setDaedalus($daedalus);

        $time = new \DateTime();

        $newFruit = new ItemConfig();
        $newFruit->setName('fruit name');

        $plant = new ItemConfig();
        $plant
            ->setName('plant name')
        ;
        $plantType = new Plant();
        $plantType->setFruit($newFruit);

        $plant->setMechanics(new ArrayCollection([$plantType]));

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;

        $gamePlant = new GameItem();
        $gamePlant
            ->setName('plant name')
            ->setEquipment($plant)
            ->setHolder($room)
        ;

        $dryConfig = new StatusConfig();
        $dryConfig->setName(EquipmentStatusEnum::PLANT_DRY);
        $status = new Status($gamePlant, $dryConfig);

        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof EquipmentEvent &&
                $event->getEquipment() === $gamePlant
            )->once()
        ;
        $this->eventService->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof EquipmentEvent &&
                $event->getEquipment()->getName() === ItemEnum::HYDROPOT
            )->once()
        ;
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        // Dried out plant
        // @TODO $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName');
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, $time);

        $this->assertCount(1, $room->getEquipments());
        $this->assertNotContains($plant, $room->getEquipments());
    }
}
