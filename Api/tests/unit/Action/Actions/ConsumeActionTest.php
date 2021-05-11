<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Consume;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;

class ConsumeActionTest extends AbstractActionTest
{
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /** @var EquipmentEffectServiceInterface | Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HEAL);
        $this->equipmentEffectService = Mockery::mock(EquipmentEffectServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Consume(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->playerService,
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

    public function testExecute()
    {
        $room = new Place();

        $daedalus = new Daedalus();

        $effect = new ConsumableEffect();
        $effect
            ->setActionPoint(1)
            ->setHealthPoint(2)
            ->setMoralPoint(3)
            ->setMovementPoint(4)
            ->setSatiety(5)
        ;

        $ration = new Ration();

        $equipment = new EquipmentConfig();
        $equipment->setMechanics(new ArrayCollection([$ration]));

        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipment)
            ->setPlace($room)
        ;

        $this->playerService->shouldReceive('persist');
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->equipmentEffectService->shouldReceive('getConsumableEffect')
            ->with($ration, $daedalus)
            ->andReturn($effect)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->times(5);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
