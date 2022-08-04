<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\MedikitHeal;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;

class MakeSickActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::MEDIKIT_HEAL);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->action = new MedikitHeal(
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

        $this->eventDispatcher->shouldReceive('dispatch');

        $player = $this->createPlayer(new Daedalus(), $room);
        $playerTarget = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $playerTarget);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
