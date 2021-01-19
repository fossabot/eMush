<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Sabotage;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class SabotageActionTest extends AbstractActionTest
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var SuccessRateServiceInterface | Mockery\Mock */
    private SuccessRateServiceInterface $successRateService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->successRateService = Mockery::mock(SuccessRateServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::SABOTAGE, 2);

        $this->action = new Sabotage(
            $this->eventDispatcher,
            $this->roomLogService,
            $this->gameEquipmentService,
            $this->playerService,
            $this->randomService,
            $this->successRateService,
            $this->statusService,
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
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setBreakableRate(20);
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //Not mush
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $mushStatus = new ChargeStatus();
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH)
        ;

        $player
            ->addStatus($mushStatus)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //Not in the same room
        $gameItem
            ->setRoom(new Room())
        ;
        $room->removeEquipment($gameItem);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $gameItem
            ->setRoom($room)
        ;
        $item->setBreakableRate(0);
        //Not breakable
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setBreakableRate(20);
        $broken = new Status();
        $broken
            ->setName(EquipmentStatusEnum::BROKEN)
        ;
        $gameItem->addStatus($broken);

        //already broken
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setBreakableRate(10);
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $mushStatus = new ChargeStatus();
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH)
        ;

        $player
            ->addStatus($mushStatus)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->roomLogService->shouldReceive('createEquipmentLog')->twice();

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $attempt = new Attempt();
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction($this->action->getActionName())
        ;
        $this->statusService->shouldReceive('createAttemptStatus')->andReturn($attempt)->once();

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->successRateService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessfull')->andReturn(false)->once();

        //Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(2, $player->getStatuses());
        $this->assertEquals(1, $attempt->getCharge());
        $this->assertEquals(8, $player->getActionPoint());

        $this->successRateService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessfull')->andReturn(true)->once();
        $this->eventDispatcher->shouldReceive('dispatch');

        //Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals(6, $player->getActionPoint());
    }
}
