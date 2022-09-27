<?php

namespace Mush\Tests\Equipment\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Listener\CycleEventSubscriber;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class EquipmentEventCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testHeavyStatusOverflowingInventory(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 0]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $heavyStatusConfig = new StatusConfig();
        $heavyStatusConfig->setName(EquipmentStatusEnum::HEAVY)->setGameConfig($gameConfig);
        $I->haveInRepository($heavyStatusConfig);

        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig->setName(PlayerStatusEnum::BURDENED)->setGameConfig($gameConfig);
        $I->haveInRepository($burdenedStatusConfig);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'name' => 'equipment_name',
            'initStatus' => new ArrayCollection([$heavyStatusConfig]),
        ]);

        $equipment = new GameItem();
        $equipment
            ->setHolder($player)
            ->setEquipment($equipmentConfig)
            ->setName('equipment_name');
        $I->haveInRepository($equipment);

        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertEmpty($player->getEquipments());
        $I->assertEquals(1, $room->getEquipments()->count());
        $I->assertEmpty($player->getStatuses());
        $I->assertEquals(1, $room->getEquipments()->first()->getStatuses()->count());
    }
}
