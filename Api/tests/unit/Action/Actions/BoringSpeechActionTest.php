<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

class BoringSpeechActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::BORING_SPEECH);

        $this->action = new BoringSpeech(
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
        $daedalus = new Daedalus();

        $room = new Place();

        $speaker = $this->createPlayer($daedalus, $room);
        $listener = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $speaker);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($listener);
        $this->eventDispatcher->shouldReceive('dispatch')->twice();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
