<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;

class TakeoffActionTest extends AbstractActionTest
{
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var PlaceServiceInterface|Mockery\Mock */
    private PlaceServiceInterface $placeService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TAKEOFF, 2, 0);
        $this->actionEntity->setCriticalRate(20);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->placeService = \Mockery::mock(PlaceServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->action = new Takeoff(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->placeService,
            $this->randomService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();

        $roomStart = new Place();
        $roomStart->setDaedalus($daedalus);
        $roomEnd = new Place();
        $roomEnd->setDaedalus($daedalus);
        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->placeService->shouldReceive('findByNameAndDaedalus')->andReturn($roomEnd);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('randomPercent')->andReturn(100);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();

        $roomStart = new Place();
        $roomStart->setDaedalus($daedalus);
        $roomEnd = new Place();
        $roomEnd->setDaedalus($daedalus);
        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->placeService->shouldReceive('findByNameAndDaedalus')->andReturn($roomEnd);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('randomPercent')->andReturn(0);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }
}
