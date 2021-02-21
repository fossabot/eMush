<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Hyperfreeze;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HyperfreezeActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var GearToolServiceInterface | Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HYPERFREEZE, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->action = new Hyperfreeze(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->statusService,
            $this->actionService,
            $this->gearToolService
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
        $room = new Place();

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setPlace($room)
            ->setName('ration')
        ;

        $gameSuperfreezer = new GameItem();
        $superfreezer = new ItemConfig();
        $superfreezer->setName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setPlace($room)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        //Not a ration
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $rationType = new Ration();
        $rationType->setIsPerishable(false);
        $ration->setMechanics(new ArrayCollection([$rationType]));

        //not perishable
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $rationType->setIsPerishable(true);
        $gameSuperfreezer->setPlace(null);
        //No superfreezer in the room

        $this->gearToolService
        ->shouldReceive('getUsedTool')
        ->andReturn(null)
        ->once()
        ;
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        //fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration
             ->setMechanics(new ArrayCollection([$rationType]))
             ->setName('fruit')
         ;
        $gameRation
            ->setEquipment($ration)
            ->setPlace($room)
            ->setName('fruit')
        ;

        $gameSuperfreezer = new GameItem();
        $superfreezer = new ItemConfig();
        $superfreezer->setName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setPlace($room)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $this->gearToolService
            ->shouldReceive('getUsedTool')
            ->andReturn($gameSuperfreezer)
            ->once()
        ;
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('persist');
        $this->statusService->shouldReceive('createCoreStatus')->once();
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(0, $player->getItems());
        $this->assertEquals($gameRation->getName(), $room->getEquipments()->first()->getName());
        $this->assertCount(0, $player->getStatuses());

        //Alien Steak
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration
             ->setMechanics(new ArrayCollection([$rationType]))
             ->setName(GameRationEnum::ALIEN_STEAK)
         ;
        $gameRation
            ->setEquipment($ration)
            ->setPlace($room)
            ->setName(GameRationEnum::ALIEN_STEAK)
        ;

        $gameSuperfreezer = new GameItem();
        $superfreezer = new ItemConfig();
        $superfreezer->setName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setPlace($room)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $gameStandardRation = new GameItem();
        $standardRation = new ItemConfig();
        $standardRation
             ->setName(GameRationEnum::STANDARD_RATION)
         ;
        $gameStandardRation
            ->setEquipment($standardRation)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $this->gearToolService
            ->shouldReceive('getUsedTool')
            ->andReturn($gameSuperfreezer)
            ->once()
        ;
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gameStandardRation)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(0, $gameSuperfreezer->getStatuses());
        $this->assertCount(0, $player->getStatuses());
    }
}
