<?php

namespace functional\Action\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Listener\ActionSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class ActionSubscriberCest
{
    private ActionSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionSubscriberInjury(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(100)
            ->setName(ActionEnum::TAKE);

        $actionEvent = new ActionEvent($action, $player, null);

        // Test injury
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => PlayerModifierLogEnum::CLUMSINESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testOnPostActionSubscriberDirty(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $dirtyConfig = new StatusConfig();
        $dirtyConfig
            ->setName(PlayerStatusEnum::DIRTY)
        ;
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$dirtyConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room, 'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);
        $action = new Action();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::TAKE)
        ;

        $actionEvent = new ActionEvent($action, $player, null);

        // Test dirty
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => StatusEventLogEnum::SOILED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testOnPostActionSubscriberAlreadyDirty(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $dirtyConfig = new StatusConfig();
        $dirtyConfig
            ->setName(PlayerStatusEnum::DIRTY)
        ;
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$dirtyConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room, 'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::TAKE)
        ;

        $dirty = new Status($player, $dirtyConfig);
        $I->haveInRepository($dirty);

        $actionEvent = new ActionEvent($action, $player, null);

        // Test already dirty
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
    }

    public function testOnPostActionSubscriberDirtyApron(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::TAKE);

        $actionEvent = new ActionEvent($action, $player, null);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON]);

        //       $gear = new Gear();
        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setReach(ModifierReachEnum::PLAYER)
            ->setDelta(-100)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setScope(ModifierScopeEnum::EVENT_DIRTY)
            ->setName(ModifierNameEnum::APRON_MODIFIER)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->refreshEntities($player);
        $I->haveInRepository($modifier);

        // Test dirty with apron
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => LogEnum::SOIL_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
