<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Transplant;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;

class PlantActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Transplant(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService
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
        $gameItem
                    ->setEquipment($item)
                    ->setRoom($room)
                    ->setName('toto');

        $fruit = new Fruit();
        $fruit->setPlantName('banana_tree');

        $plant = new ItemConfig();
        $plant->setName('plant');

        $gameHydropot = new GameItem();
        $hydropot = new ItemConfig();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setEquipment($hydropot)
                    ->setRoom($room)
                    ->setName(ItemEnum::HYDROPOT);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //Not a blueprint
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setMechanics(new ArrayCollection([$fruit]));

        //Hydropot in another room
        $gameHydropot->setRoom(new Room());

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
                    ->setEquipment($item)
                    ->setRoom($room)
                    ->setName('toto');

        $fruit = new Fruit();
        $fruit->setPlantName('banana_tree');

        $item->setMechanics(new ArrayCollection([$fruit]));

        $plant = new ItemConfig();
        $plant->setName('banana_tree');
        $gamePlant = new GameItem();
        $gamePlant->setEquipment($plant);

        $gameHydropot = new GameItem();
        $hydropot = new ItemConfig();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setEquipment($hydropot)
                    ->setRoom($room)
                    ->setName(ItemEnum::HYDROPOT);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gamePlant)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertContains($gamePlant, $player->getRoom()->getEquipments());
    }
}
