<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ExpressCook;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ExpressCookActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface|Mockery\Mock */
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
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->playerService,
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

    public function testExecuteFruit()
    {
        //frozen fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setPlayer($player)
            ->setName('ration')
        ;

        $frozenStatus = new Status($gameRation);
        $frozenStatus
             ->setName(EquipmentStatusEnum::FROZEN)
        ;

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setPlace($room)
        ;

        $chargeStatus = new ChargeStatus($gameMicrowave);
        $chargeStatus
            ->setName(EquipmentStatusEnum::CHARGES)
            ->setCharge(3)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->playerService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getItems()->first()->getStatuses());
        $this->assertEquals($gameRation->getName(), $player->getItems()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }

    public function testExecuteRation()
    {
        //Standard Ration
        $daedalus = new Daedalus();
        $room = new Place();

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setEquipment($ration)
            ->setPlace($room)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setPlace($room)
        ;

        $chargeStatus = new ChargeStatus($gameMicrowave);
        $chargeStatus
            ->setName(EquipmentStatusEnum::CHARGES)
            ->setCharge(3)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $gameCookedRation = new GameItem();
        $cookedRation = new ItemConfig();
        $cookedRation
             ->setName(GameRationEnum::COOKED_RATION)
         ;
        $gameCookedRation
            ->setEquipment($cookedRation)
            ->setName(GameRationEnum::COOKED_RATION)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gameCookedRation)->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(1, $gameMicrowave->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
