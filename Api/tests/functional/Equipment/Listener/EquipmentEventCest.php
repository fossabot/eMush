<?php

namespace functional\Equipment\Listener;

use App\Tests\FunctionalTester;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Game\Service\EventServiceInterface;

class EquipmentEventCest
{
    private EventServiceInterface $eventServiceService;

    public function _before(FunctionalTester $I)
    {
        $this->eventServiceService = $I->grabService(EventServiceInterface::class);
    }

    public function testDispatchEquipmentCreated(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'name' => 'equipment_name']);

        $equipment = new GameItem();
        $equipment
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getName());
        $I->haveInRepository($equipment);

        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventServiceService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getEquipments());

        // Case of a game Item
        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig, 'name' => 'item_name']);

        $equipment = $equipmentConfig
            ->createGameItem()
            ->setHolder($player);
        $I->haveInRepository($equipment);

        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventServiceService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $player->getEquipments());

        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig, 'name' => 'item_name']);

        $equipment = $equipmentConfig->createGameItem()
            ->setHolder($player);
        $I->haveInRepository($equipment);

        // Case of a game Item full inventory
        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            ActionEnum::DISASSEMBLE,
            new \DateTime()
        );
        $this->eventServiceService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(2, $room->getEquipments());
        $I->assertCount(1, $player->getEquipments());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room,
            'player' => $player,
            'log' => LogEnum::OBJECT_FELL,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchEquipmentDestroyed(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        // Case of a game Equipment
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent(
            $gameEquipment,
            false,
            VisibilityEnum::PUBLIC,
            StatusEnum::FIRE,
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertCount(0, $room->getEquipments());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => LogEnum::EQUIPMENT_DESTROYED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
