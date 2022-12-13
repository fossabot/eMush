<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoTheThingCest
{
    private DoTheThing $doTheThingAction;
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->doTheThingAction = $I->grabService(DoTheThing::class);
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDoTheThing(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, DaedalusConfigFixtures::class, LocalizationConfigFixtures::class]);

        $didTheThingStatus = new ChargeStatusConfig();
        $didTheThingStatus
            ->setStatusName(PlayerStatusEnum::DID_THE_THING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($didTheThingStatus);
        $pregnantStatus = new StatusConfig();
        $pregnantStatus
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($pregnantStatus);
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($attemptConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('disease')
            ->buildName(GameConfigEnum::TEST)
                ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setCauseName('sex')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $pregnantStatus, $didTheThingStatus]))
            ->setDiseaseConfig(new ArrayCollection([$diseaseConfig]))
            ->setDiseaseCauseConfig(new ArrayCollection([$diseaseCauseConfig]))
            ->setDaedalusConfig($daedalusConfig)
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->buildName()
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertNull($this->doTheThingAction->cannotExecuteReason());

        $this->doTheThingAction->execute();

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(8, $player->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::DO_THE_THING_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // Check if pregnancy log works
        $pregnantStatusEvent = new StatusEvent(
            PlayerStatusEnum::PREGNANT,
            $player,
            $this->doTheThingAction->getActionName(),
            new \DateTime()
        );
        $pregnantStatusEvent->setVisibility(VisibilityEnum::PRIVATE);

        $this->eventDispatcher->dispatch($pregnantStatusEvent, StatusEvent::STATUS_APPLIED);

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => StatusEventLogEnum::BECOME_PREGNANT,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testNoFlirt(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->buildName()
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED,
            $this->doTheThingAction->cannotExecuteReason()
        );
    }

    public function testWitness(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->buildName()
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        /** @var Player $targetPlayer */
        $witness = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(ActionImpossibleCauseEnum::DO_THE_THING_WITNESS,
            $this->doTheThingAction->cannotExecuteReason()
        );
    }

    public function testRoomHasBed(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->buildName()
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
            'moralPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->doTheThingAction->isVisible());
    }
}
