<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\InsertOxygen;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;

class InsertOxygenTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var DaedalusServiceInterface | Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INSERT_OXYGEN);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->daedalusService = Mockery::mock(DaedalusServiceInterface::class);

        $this->action = new InsertOxygen(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->daedalusService
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
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setIsHeavy(false)
        ;

        $player = $this->createPlayer($daedalus, $room);
        $gameItem
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($player)
        ;

        $daedalus->setOxygen(10);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(32);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus->setGameConfig($gameConfig);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::OXYGEN_TANK);
        $gameTank = new GameEquipment();
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setPlace($room)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('delete');
        $this->daedalusService->shouldReceive('changeOxygenLevel')->andReturn($daedalus);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
