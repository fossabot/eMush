<?php

namespace Mush\Test\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;

class RationCycleHandlerTest extends TestCase
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface|Mockery\Mock $eventService;

    private RationCycleHandler $rationCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->eventService = Mockery::mock(EventServiceInterface::class);

        $this->rationCycleHandler = new RationCycleHandler(
            $this->gameEquipmentService,
            $this->eventService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNewDay()
    {
        $fruit = new ItemConfig();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $gameFruit = new GameItem();
        $gameFruit
            ->setEquipment($fruit)
        ;

        $frozenConfig = new StatusConfig();
        $frozenConfig->setName(EquipmentStatusEnum::FROZEN);
        $frozen = new Status($gameFruit, $frozenConfig);

        $unstableConfig = new StatusConfig();
        $unstableConfig->setName(EquipmentStatusEnum::UNSTABLE);
        $unstable = new Status(new GameItem(), $unstableConfig);
        $hazardousConfig = new StatusConfig();
        $hazardousConfig->setName(EquipmentStatusEnum::HAZARDOUS);
        $hazardous = new Status(new GameItem(), $hazardousConfig);
        $decomposingConfig = new StatusConfig();
        $decomposingConfig->setName(EquipmentStatusEnum::DECOMPOSING);
        $decomposing = new Status(new GameItem(), $decomposingConfig);

        // frozen
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(1, $gameFruit->getStatuses());

        $gameFruit->removeStatus($frozen);

        // unfrozen day 1
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::UNSTABLE && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());

        $gameFruit->addStatus($unstable);

        // day 2
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::HAZARDOUS && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());

        $gameFruit->addStatus($hazardous);

        // day 3
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::DECOMPOSING && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());
    }
}
