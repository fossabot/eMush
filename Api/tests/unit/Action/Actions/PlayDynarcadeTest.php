<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\PlayDynarcade;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

use Mush\Action\ActionResult\{
    Fail,
    Success
};
use Mush\Equipment\Entity\{
    Config\ItemConfig,
    GameItem
};

class PlayDynarcadeTest extends AbstractActionTest
{
      /** @var RandomServiceInterface|Mockery\Mock */
      private RandomServiceInterface $randomService;
      
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::PLAY_ARCADE);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->action = new PlayDynarcade(
            $this->eventService,
            $this->actionService,
            $this->validator,
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

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->actionService->shouldIgnoreMissing();

        $expectedPlayerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            $this->action->getAction()->getActionTags(),
            new \DateTime()
        );
        $expectedPlayerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);

        $this->eventService->shouldReceive('callEvent')
        ->withArgs([\Mockery::on(function (PlayerVariableEvent $event) use($expectedPlayerModifierEvent) {
           return $event->getPlayer() == $expectedPlayerModifierEvent->getPlayer()
                && $event->getVariableName() == $expectedPlayerModifierEvent->getVariableName()
                && $event->getQuantity() == $expectedPlayerModifierEvent->getQuantity()
                && $event->getTags() == $expectedPlayerModifierEvent->getTags()
                && $event->getVisibility() == $expectedPlayerModifierEvent->getVisibility();
        }), VariableEventInterface::CHANGE_VARIABLE])
        ->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->actionService->shouldIgnoreMissing();

        $expectedPlayerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            2,
            $this->action->getAction()->getActionTags(),
            new \DateTime()
        );
        $expectedPlayerModifierEvent->setVisibility(VisibilityEnum::PUBLIC);
        
        $this->eventService->shouldReceive('callEvent')
        ->withArgs([\Mockery::on(function (PlayerVariableEvent $event) use($expectedPlayerModifierEvent) {
           return $event->getPlayer() == $expectedPlayerModifierEvent->getPlayer()
                && $event->getVariableName() == $expectedPlayerModifierEvent->getVariableName()
                && $event->getQuantity() == $expectedPlayerModifierEvent->getQuantity()
                && $event->getTags() == $expectedPlayerModifierEvent->getTags()
                && $event->getVisibility() == $expectedPlayerModifierEvent->getVisibility();
        }), VariableEventInterface::CHANGE_VARIABLE])
        ->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
