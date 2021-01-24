<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class SearchActionTest extends AbstractActionTest
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

        $this->actionEntity = $this->createActionEntity(ActionEnum::SEARCH, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Search(
            $this->eventDispatcher,
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

    public function testExecute()
    {
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //No item in the room
        $result = $this->action->execute();
        $this->assertInstanceOf(Fail::class, $result);

        //No hidden item in the room
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $result = $this->action->execute();
        $this->assertInstanceOf(Fail::class, $result);

        //Success find
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $hidden = new Status($gameItem);
        $hiddenBy = $this->createPlayer(new Daedalus(), new Room());
        $hidden
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setTarget($hiddenBy)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->statusService->shouldReceive('getMostRecent')->andReturn($gameItem)->once();
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());
        $this->assertCount(0, $hiddenBy->getStatuses());

        //2 hidden items
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $hiddenBy = $this->createPlayer(new Daedalus(), new Room());
        $hidden = new Status($gameItem);
        $hidden
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setTarget($hiddenBy)
        ;

        $gameItem2 = new GameItem();
        $gameItem2
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $hidden2 = new Status($gameItem2);
        $hidden2
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setTarget($hiddenBy)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->statusService->shouldReceive('getMostRecent')->andReturn($gameItem)->once();
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(1, $room->getEquipments()->last()->getStatuses());
        $this->assertEquals($hidden2, $hiddenBy->getTargetingStatuses()->first());
    }
}
