<?php

namespace functional\Action\Listener;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Listener\ActionSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class ActionSubscriberCest
{
    private ActionSubscriber $actionSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->actionSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionSubscriberInjury(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 10,
            'characterConfig' => $characterConfig,
        ]);
        $action = new Action();

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(100)
            ->setName(ActionEnum::TAKE);

        $actionEvent = new ActionEvent($action, $player, null);

        // Test injury
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        codecept_debug($I->grabEntitiesFromRepository(RoomLog::class));
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => PlayerModifierLogEnum::CLUMSINESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testOnPostActionSubscriberDirty(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2, 'characterConfig' => $characterConfig]);
        $action = new Action();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::TAKE)
        ;

        $statusDirty = new StatusConfig();
        $statusDirty
            ->setName(PlayerStatusEnum::DIRTY)
            ->setGameConfig($gameConfig);
        $I->haveInRepository($statusDirty);

        $actionEvent = new ActionEvent($action, $player, null);

        // Test dirty
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => StatusEventLogEnum::SOILED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testOnPostActionSubscriberAlreadyDirty(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2, 'characterConfig' => $characterConfig]);
        $action = new Action();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::TAKE)
        ;

        $dirtyConfig = new StatusConfig();
        $dirtyConfig->setGameConfig($gameConfig)->setName(PlayerStatusEnum::DIRTY);
        $I->haveInRepository($dirtyConfig);
        $dirty = new Status($player, $dirtyConfig);
        $I->haveInRepository($dirty);

        $actionEvent = new ActionEvent($action, $player, null);

        // Test already dirty
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
    }

    public function testOnPostActionSubscriberDirtyApron(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2, 'characterConfig' => $characterConfig]);
        $action = new Action();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::TAKE);

        $actionEvent = new ActionEvent($action, $player, null);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON]);

        //       $gear = new Gear();
        $modifierConfig = new ModifierConfig(
            ModifierNameEnum::APRON_MODIFIER,
            ModifierReachEnum::PLAYER,
            0,
            ModifierModeEnum::SET_VALUE,
        );
        $modifierConfig
            ->addTargetEvent(EnhancePercentageRollEvent::DIRTY_ROLL_RATE)
            ->setLogKeyWhenApplied(ModifierNameEnum::APRON_MODIFIER);
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        // Test dirty with apron
        $this->actionSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
    }
}
