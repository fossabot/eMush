<?php

namespace Mush\Unit\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\CycleHandler\PlantCycleHandler;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class PlantCycleHandlerTest extends TestCase
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var DaedalusServiceInterface | Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;
    /** @var EquipmentEffectServiceInterface | Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;

    private PlantCycleHandler $plantCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->equipmentEffectService = Mockery::mock(EquipmentEffectServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->daedalusService = Mockery::mock(DaedalusServiceInterface::class);

        $this->plantCycleHandler = new PlantCycleHandler(
            $this->gameEquipmentService,
            $this->randomService,
            $this->roomLogService,
            $this->statusService,
            $this->daedalusService,
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

        $this->roomLogService->shouldReceive('createLog');
        $this->gameEquipmentService->shouldReceive('persist')->twice();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once(); //Plant should not get disease

        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setPlantDiseaseRate(50);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $gamePlant = new GameItem();
        $gamePlant
            ->setEquipment($plant)
        ;

        $chargeStatus = new ChargeStatus($gamePlant);
        $chargeStatus->setName(EquipmentStatusEnum::PLANT_YOUNG);
        $chargeStatus->setCharge(1);

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

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

        $chargeStatus->setCharge(10);

        $gamePlant
            ->setEquipment($plant)
            ->setPlace(new Place())
        ;

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->with(EquipmentStatusEnum::PLANT_DISEASED, $gamePlant)
            ->once()
        ;

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

        $this->assertTrue(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => EquipmentStatusEnum::PLANT_YOUNG === $status->getName())
                ->isEmpty()
        );
    }

    public function testNewDay()
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
        $this->roomLogService->shouldReceive('createLog');
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn(new GameItem());
        $this->gameEquipmentService->shouldReceive('createGameEquipment')->andReturn(new GameItem());

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
        $this->equipmentEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $gamePlant = new GameItem();
        $gamePlant
            ->setEquipment($plant)
            ->setPlace($room)
        ;

        $status = new Status(new GameItem());
        $status->setName(EquipmentStatusEnum::PLANT_THIRSTY);
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->with(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant)
            ->andReturn($status)
            ->once()
        ;
        $this->daedalusService->shouldReceive('changeOxygenLevel')->andReturn($daedalus)->once();

        //Mature Plant, no problem
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(2, $room->getEquipments());

        $dried = new Status(new GameItem());
        $dried->setName(EquipmentStatusEnum::PLANT_DRIED_OUT);
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->with(EquipmentStatusEnum::PLANT_DRIED_OUT, $gamePlant)->andReturn($dried)
            ->once()
        ;

        $this->daedalusService->shouldReceive('changeOxygenLevel')->andReturn($daedalus)->once();
        //Thirsty plant
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(2, $room->getEquipments());

        $this->gameEquipmentService->shouldReceive('createEquipment')->andReturn(new GameItem());
        $this->gameEquipmentService->shouldReceive('delete');

        //Dried out plant
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(2, $room->getEquipments());
        $this->assertNotContains($plant, $room->getEquipments());
    }
}
