<?php

namespace Mush\Tests\Place\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RoomEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testRoomEventOnNonRoomPlace(FunctionalTester $I)
    {
        $time = new DateTime();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'type' => PlaceTypeEnum::SPACE]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'healthPoint' => 10]);

        $roomEvent = new RoomEvent($room, $time);

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
        }
        );

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }
        );

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);
        }
        );
    }

    public function testNewFire(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $time = new DateTime();
        /** @var Place $room */
        $room = $I->have(Place::class);

        $room->setDaedalus($daedalus);

        $roomEvent = new RoomEvent($room, $time);

        $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);

        $I->assertEquals(1, $room->getStatuses()->count());

        /** @var Status $fireStatus */
        $fireStatus = $room->getStatuses()->first();

        $I->assertEquals($room, $fireStatus->getOwner());
        $I->assertEquals(StatusEnum::FIRE, $fireStatus->getName());
    }

    public function testTremor(FunctionalTester $I)
    {
        $time = new DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'healthPoint' => 10, 'characterConfig' => $characterConfig]);

        $roomEvent = new RoomEvent($room, $time);

        $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => LogEnum::TREMOR_GRAVITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testElectricArc(FunctionalTester $I)
    {
        $time = new DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'healthPoint' => 10, 'characterConfig' => $characterConfig]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setPlace($room)
        ;
        $I->haveInRepository($gameEquipment);

        $roomEvent = new RoomEvent($room, $time);
        $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);

        $I->assertEquals(7, $player->getHealthPoint());
        $I->assertTrue($gameEquipment->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => LogEnum::ELECTRIC_ARC,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
