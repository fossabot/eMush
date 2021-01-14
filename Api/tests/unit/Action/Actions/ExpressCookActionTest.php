<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ExpressCook;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExpressCookActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::EXPRESS_COOK);

        $this->action = new ExpressCook(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->statusService
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

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setRoom($room)
            ->setName('ration')
        ;

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(EquipmentStatusEnum::CHARGES)
             ->setCharge(3);

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;

        $chargeStatus->setGameEquipment($gameMicrowave);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //not possible to cook (not frozen nor standard ration)
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $frozenStatus = new Status();
        $frozenStatus
             ->setName(EquipmentStatusEnum::FROZEN)
             ->setGameEquipment($gameRation);
        $gameRation->addStatus($frozenStatus);

        $gameMicrowave->setRoom(null);
        //No microwave in the room
        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([]))->once();
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        //frozen fruit
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setPlayer($player)
            ->setName('ration')
        ;

        $frozenStatus = new Status();
        $frozenStatus
             ->setName(EquipmentStatusEnum::FROZEN)
             ->setGameEquipment($gameRation);
        $gameRation->addStatus($frozenStatus);

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(EquipmentStatusEnum::CHARGES)
             ->setCharge(3);

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;
        $chargeStatus->setGameEquipment($gameMicrowave);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([$gameMicrowave]))->twice();
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->playerService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('delete')->once();
        $this->statusService->shouldReceive('persist')->twice();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getItems()->first()->getStatuses());
        $this->assertEquals(2, $room->getEquipments()->first()->getStatuses()->first()->getCharge());
        $this->assertEquals($gameRation->getName(), $player->getItems()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());

        //Standard Ration
        $daedalus = new Daedalus();
        $room = new Room();

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setEquipment($ration)
            ->setRoom($room)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(EquipmentStatusEnum::CHARGES)
             ->setCharge(3);

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;
        $chargeStatus->setGameEquipment($gameMicrowave);

        $player = $this->createPlayer(new Daedalus(), $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $gameCookedRation = new GameItem();
        $cookedRation = new ItemConfig();
        $cookedRation
             ->setName(GameRationEnum::COOKED_RATION)
         ;
        $gameCookedRation
            ->setEquipment($cookedRation)
            ->setName(GameRationEnum::COOKED_RATION)
        ;

        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([$gameMicrowave]))->twice();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gameCookedRation)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(1, $gameMicrowave->getStatuses());
        $this->assertEquals(2, $gameMicrowave->getStatuses()->first()->getCharge());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
