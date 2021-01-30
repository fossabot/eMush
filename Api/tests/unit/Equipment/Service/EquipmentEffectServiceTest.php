<?php

namespace Mush\Test\Equipment\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Repository\ConsumableEffectRepository;
use Mush\Equipment\Repository\PlantEffectRepository;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Game\Service\RandomServiceInterface;
use PHPUnit\Framework\TestCase;

class EquipmentEffectServiceTest extends TestCase
{
    /** @var ConsumableEffectRepository | Mockery\Mock */
    private ConsumableEffectRepository $consumableEffectRepository;
    /** @var PlantEffectRepository | Mockery\Mock */
    private PlantEffectRepository $plantEffectRepository;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    private EquipmentEffectService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->consumableEffectRepository = Mockery::mock(ConsumableEffectRepository::class);
        $this->plantEffectRepository = Mockery::mock(PlantEffectRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new EquipmentEffectService(
            $this->consumableEffectRepository,
            $this->plantEffectRepository,
            $this->randomService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetConsumableEffect()
    {
        $daedalus = new Daedalus();
        $ration = new Ration();

        $ration
            ->setHealthPoints([0 => 1, 1 => 1, 2 => 1])
            ->setMoralPoints([0 => 5, 1 => 1, 2 => 1])
            ->setActionPoints([0 => 1, 1 => 0])
            ->setMovementPoints([1 => 1])
            ->setMovementPoints([1 => 1])
            ->setDiseasesChances(['disease' => 55])
            ->setDiseasesDelayMin(['disease' => 0])
            ->setDiseasesDelayLength(['disease' => 0])
            ->setExtraEffects(['break_door' => 55])
        ;
        $consumableEffectFromRepository = new ConsumableEffect();
        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn($consumableEffectFromRepository)
            ->once()
        ;

        $consumableEffect = $this->service->getConsumableEffect($ration, $daedalus);

        $this->assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        $this->assertEquals($consumableEffectFromRepository, $consumableEffect);

        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;
        $this->consumableEffectRepository
            ->shouldReceive('persist')
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(2)
            ->times(4)
        ;
        $consumableEffect = $this->service->getConsumableEffect($ration, $daedalus);

        $this->assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        $this->assertEquals($daedalus, $consumableEffect->getDaedalus());
        $this->assertEquals($ration, $consumableEffect->getRation());
        $this->assertEquals(2, $consumableEffect->getActionPoint());
        $this->assertEquals(2, $consumableEffect->getMovementPoint());
        $this->assertEquals(2, $consumableEffect->getHealthPoint());
        $this->assertEquals(2, $consumableEffect->getMoralPoint());

        //test fruit
        $fruit = new Fruit();

        $fruit
            ->setDiseasesChances([100 => 64, 25 => 1])
            ->setDiseasesName([
                        'disease1' => 1,
                        'disease2' => 6, ])
            ->setDiseasesDelayMin([0 => 1, 5 => 1])
            ->setDiseasesDelayLength([7 => 1])
            ->setFruitEffectsNumber([0 => 35, 1 => 40, 2 => 15])
            ->setExtraEffects(['extraActionPoint' => 50])
        ;

        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;
        $this->consumableEffectRepository
            ->shouldReceive('persist')
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(0, 0, 0, 0, 4, 50, 50, 2, 4, 50, 2, 4)
            ->times(12)
        ;
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([1, 3, 4, 5])
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaArray')
            ->andReturn(['disease1'], ['disease1', 'disease2'])
            ->times(2)
        ;
        $consumableEffect = $this->service->getConsumableEffect($fruit, $daedalus);

        $this->assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        $this->assertEquals($daedalus, $consumableEffect->getDaedalus());
        $this->assertEquals($fruit, $consumableEffect->getRation());
        $this->assertEquals(0, $consumableEffect->getActionPoint());
        $this->assertEquals(0, $consumableEffect->getMovementPoint());
        $this->assertEquals(0, $consumableEffect->getHealthPoint());
        $this->assertEquals(0, $consumableEffect->getMoralPoint());
        $this->assertEquals(['disease1' => 50], $consumableEffect->getCures());
        $this->assertEquals(['disease1' => 50, 'disease2' => 50], $consumableEffect->getDiseasesChance());
        $this->assertEquals(['disease1' => 2, 'disease2' => 2], $consumableEffect->getDiseasesDelayMin());
        $this->assertEquals(['disease1' => 4, 'disease2' => 4], $consumableEffect->getDiseasesDelayLength());
        $this->assertEquals(['extraActionPoint' => 50], $consumableEffect->getExtraEffects());

        //test drugs
        $drug = new Drug();
        $drug->setMoralPoints([0 => 97, -2 => 1, 1 => 1])
            ->setActionPoints([0 => 98, 1 => 1])
            ->setMovementPoints([0 => 98, 2 => 1])
            ->setCures([
                'disease1' => 100,
                'disease2' => 100,
                'disease3' => 100, ])
            ->setDrugEffectsNumber([1 => 60, 2 => 30, 3 => 8])
        ;
        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;
        $this->consumableEffectRepository
            ->shouldReceive('persist')
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(0, 0, 0, 0, 2)
            ->times(5)
        ;
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn(['disease1', 'disease2'])
            ->once()
        ;
        $consumableEffect = $this->service->getConsumableEffect($drug, $daedalus);

        $this->assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        $this->assertEquals($daedalus, $consumableEffect->getDaedalus());
        $this->assertEquals($drug, $consumableEffect->getRation());
        $this->assertEquals(0, $consumableEffect->getActionPoint());
        $this->assertEquals(0, $consumableEffect->getMovementPoint());
        $this->assertEquals(0, $consumableEffect->getHealthPoint());
        $this->assertEquals(0, $consumableEffect->getMoralPoint());
        $this->assertEquals(['disease1' => 100, 'disease2' => 100], $consumableEffect->getCures());
    }

    public function testGetPlantEffect()
    {
        $daedalus = new Daedalus();
        $plant = new Plant();

        $plant
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $plantEffectFromRepository = new PlantEffect();
        $this->plantEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn($plantEffectFromRepository)
            ->once()
        ;

        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        $this->assertInstanceOf(PlantEffect::class, $plantEffect);
        $this->assertEquals($plantEffectFromRepository, $plantEffect);

        $this->plantEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;
        $this->plantEffectRepository
            ->shouldReceive('persist')
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(8)
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(1)
            ->once()
        ;
        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        $this->assertInstanceOf(PlantEffect::class, $plantEffect);
        $this->assertEquals($daedalus, $plantEffect->getDaedalus());
        $this->assertEquals($plant, $plantEffect->getPlant());
        $this->assertEquals(1, $plantEffect->getOxygen());
        $this->assertEquals(8, $plantEffect->getMaturationTime());
    }
}
