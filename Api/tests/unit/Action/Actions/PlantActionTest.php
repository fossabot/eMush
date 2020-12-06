<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Transplant;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Items\Fruit;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlantActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->itemService = Mockery::mock(GameItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Transplant(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
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
        $item = new Item();
        $gameItem
                    ->setItem($item)
                    ->setRoom($room)
                    ->setName('toto');

        $fruit = new Fruit();
        $fruit->setPlantName('banana_tree');

        $plant = new Item();
        $plant->setName('plant');

        $gameHydropot = new GameItem();
        $hydropot = new Item();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setItem($hydropot)
                    ->setRoom($room)
                    ->setName(ItemEnum::HYDROPOT);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($player, $actionParameter);

        //Not a blueprint
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setTypes(new ArrayCollection([$fruit]));

        //Hydropot in another room
        $gameHydropot->setRoom(new Room());

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
                    ->setItem($item)
                    ->setRoom($room)
                    ->setName('toto');

        $fruit = new Fruit();
        $fruit->setPlantName('banana_tree');

        $item->setTypes(new ArrayCollection([$fruit]));

        $plant = new Item();
        $plant->setName('banana_tree');
        $gamePlant = new GameItem();
        $gamePlant->setItem($plant);

        $gameHydropot = new GameItem();
        $hydropot = new Item();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setItem($hydropot)
                    ->setRoom($room)
                    ->setName(ItemEnum::HYDROPOT);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = new Player();
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $this->itemService->shouldReceive('createGameItemFromName')->andReturn($gamePlant)->once();
        $this->itemService->shouldReceive('delete');

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertEquals($player->getRoom()->getItems()->first()->getItem(), $plant);
    }

    private function createPlayer(Daedalus $daedalus, Room $room): Player
    {
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setRoom($room)
        ;

        return $player;
    }
}
