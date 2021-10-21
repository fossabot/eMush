<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\UseBandage;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

class UseBandageActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SELF_HEAL);

        $this->action = new UseBandage(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
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
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('item')
            ->setEquipment($item)
            ->setHolder($room);

        $this->eventDispatcher->shouldReceive('dispatch');

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch');
        $this->eventDispatcher->shouldReceive('dispatch');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
