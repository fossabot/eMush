<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\SelfSurgery;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\TypeEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;

class SelfSurgeryActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface|Mockery\Mock $randomService;

    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface|Mockery\Mock $modifierService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SELF_SURGERY);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->action = new SelfSurgery(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->modifierService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecuteFail()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = new GameEquipment();
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionEntity]));
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::SURGERY_PLOT)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setHolder($room)
            ->setName(EquipmentEnum::SURGERY_PLOT)
        ;

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->randomService->shouldReceive('outputCriticalChances')
            ->andReturn(ActionOutputEnum::FAIL)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->times(3);

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = new GameEquipment();
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionEntity]));
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::SURGERY_PLOT)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setHolder($room)
            ->setName(EquipmentEnum::SURGERY_PLOT)
        ;

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->randomService->shouldReceive('outputCriticalChances')
            ->andReturn(ActionOutputEnum::SUCCESS)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->times(3);

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertNotInstanceOf(CriticalSuccess::class, $result);
    }

    public function testExecuteCriticalSuccess()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = new GameEquipment();
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionEntity]));
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::SURGERY_PLOT)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setHolder($room)
            ->setName(EquipmentEnum::SURGERY_PLOT)
        ;

        $diseaseConfig1 = new DiseaseConfig();
        $diseaseConfig1->setType(TypeEnum::DISEASE);
        $playerDisease1 = new PlayerDisease();
        $playerDisease1->setDiseaseConfig($diseaseConfig1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setType(TypeEnum::INJURY);
        $playerDisease2 = new PlayerDisease();
        $playerDisease2->setDiseaseConfig($diseaseConfig2);

        $player->addMedicalCondition($playerDisease1)->addMedicalCondition($playerDisease2);

        $this->randomService->shouldReceive('outputCriticalChances')
            ->andReturn(ActionOutputEnum::CRITICAL_SUCCESS)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->times(3);

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(CriticalSuccess::class, $result);
    }
}
