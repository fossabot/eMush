<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RemoveSpore;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

class RemoveSporeActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REMOVE_SPORE, 1);

        $this->action = new RemoveSpore(
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
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $player->setSpores(1);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->times(2);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
