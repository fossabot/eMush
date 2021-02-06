<?php

namespace Mush\Test\Daedalus\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusWidgetService;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusWidgetServiceTest extends TestCase
{
    /** @var TranslatorInterface | Mockery\Mock */
    private TranslatorInterface $translator;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    private DaedalusWidgetService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->translator = Mockery::mock(TranslatorInterface::class);

        $this->service = new DaedalusWidgetService(
            $this->translator,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testgetMinimap()
    {
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room2 = new Place();
        $room2->setName(RoomEnum::BRIDGE);

        $daedalus = new Daedalus();
        $daedalus
            ->addPlace($room)
            ->addPlace($room2)
        ;

        $player = new Player();
        $room2->addPlayer($player);

        $minimap = $this->service->getMinimap($daedalus);

        $this->assertIsArray($minimap);
        $this->assertArrayHasKey(RoomEnum::LABORATORY, $minimap);
        $this->assertEquals(0, $minimap[RoomEnum::LABORATORY]['players']);
        $this->assertArrayHasKey(RoomEnum::BRIDGE, $minimap);
        $this->assertEquals(1, $minimap[RoomEnum::BRIDGE]['players']);
    }
}
