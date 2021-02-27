<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\LieDown;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class LieDownActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::LIE_DOWN);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new LieDown(
            $this->eventDispatcher,
            $this->statusService,
            $this->actionService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCannotExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = new GameEquipment();
        $tool = new Tool();
        $lieDownAction = new Action();
        $lieDownAction->setName(ActionEnum::LIE_DOWN);
        $tool->setActions(new ArrayCollection([$lieDownAction]));
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setPlace($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status = new Status($player);
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        //Bed already occupied
        $status->setTarget($gameEquipment);
        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);

        //player already lying down
        $status->setTarget(null);
        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = new GameEquipment();
        $tool = new Tool();
        $lieDownAction = new Action();
        $lieDownAction->setName(ActionEnum::LIE_DOWN);
        $tool->setActions(new ArrayCollection([$lieDownAction]));
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setPlace($room)
            ->setName(EquipmentEnum::BED)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->statusService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getStatuses());
        $this->assertCount(1, $gameEquipment->getTargetingStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
