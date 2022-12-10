<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;

class ConsumeChargeOnActionCest
{
    private Coffee $coffeeAction;

    public function _before(FunctionalTester $I)
    {
        $this->coffeeAction = $I->grabService(Coffee::class);
    }

    public function testToolCharge(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::COFFEE)
        ;
        $I->haveInRepository($statusConfig);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(2);
        $actionEntity = new Action();
        $actionEntity
            ->setName(ActionEnum::COFFEE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionEntity);

        $tool = new Tool();
        $tool->addAction($actionEntity);
        $I->haveInRepository($tool);

        $equipment = new EquipmentConfig();
        $equipment
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setMechanics(new ArrayCollection([$tool]))
        ;
        $I->haveInRepository($equipment);
        $equipmentCoffee = new ItemConfig();
        $equipmentCoffee
            ->setName(GameRationEnum::COFFEE)
        ;
        $I->haveInRepository($equipmentCoffee);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]))
            ->setEquipmentsConfig(new ArrayCollection([$equipment, $equipmentCoffee]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
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
            'actionPoint' => 10,
            'healthPoint' => 10,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;
        $I->haveInRepository($gameEquipment);

        $chargeStatus = new ChargeStatus($gameEquipment, $statusConfig);
        $chargeStatus
            ->setCharge(2)
        ;
        $I->haveInRepository($chargeStatus);

        $this->coffeeAction->loadParameters($actionEntity, $player, $gameEquipment);

        $I->assertEquals(0, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(2, $this->coffeeAction->getActionPointCost());
        $I->assertEquals(2, $chargeStatus->getCharge());

        $this->coffeeAction->execute();

        $I->assertEquals(1, $chargeStatus->getCharge());
    }

    public function testGearCharge(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $equipmentCoffee = new EquipmentConfig();
        $equipmentCoffee
            ->setName(GameRationEnum::COFFEE)
        ;
        $I->haveInRepository($equipmentCoffee);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(2);
        $actionEntity = new Action();
        $actionEntity
            ->setName(ActionEnum::COFFEE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionEntity);

        $equipment = new EquipmentConfig();
        $equipment
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setActions(new ArrayCollection([$actionEntity]))
        ;
        $I->haveInRepository($equipment);
        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setScope(ActionEnum::COFFEE)
            ->setReach(ReachEnum::INVENTORY)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $gearMechanic = new Gear();
        $gearMechanic->setModifierConfigs(new arrayCollection([$modifierConfig]));
        $gearConfig = new ItemConfig();
        $gearConfig
            ->setName(GearItemEnum::SOAP)
            ->setMechanics(new ArrayCollection([$gearMechanic]))
        ;
        $I->haveInRepository($modifierConfig);
        $I->haveInRepository($gearMechanic);
        $I->haveInRepository($gearConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig]))
            ->setEquipmentsConfig(new ArrayCollection([$equipment, $equipmentCoffee, $gearConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
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
            'actionPoint' => 10,
            'healthPoint' => 10,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;
        $I->haveInRepository($gameEquipment);

        $I->refreshEntities($room);

        $gameGear = new GameItem($player);
        $gameGear
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($gearConfig)
        ;
        $I->haveInRepository($gameGear);

        $I->refreshEntities($player);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::COFFEE)
        ;
        $I->haveInRepository($statusConfig);
        $chargeStatus = new ChargeStatus($gameGear, $statusConfig);
        $chargeStatus
            ->setCharge(1)
        ;
        $I->haveInRepository($chargeStatus);

        $modifier = new Modifier($player, $modifierConfig);
        $modifier->setCharge($chargeStatus);
        $I->haveInRepository($modifier);

        $this->coffeeAction->loadParameters($actionEntity, $player, $gameEquipment);

        $I->assertEquals(0, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(1, $this->coffeeAction->getActionPointCost());
        $I->assertEquals(1, $chargeStatus->getCharge());

        $this->coffeeAction->execute();

        $I->assertEquals(0, $chargeStatus->getCharge());
        $I->assertEquals(0, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(2, $this->coffeeAction->getActionPointCost());
    }

    public function testGearMovementActionConversionCharge(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);
        $actionEntity = new Action();
        $actionEntity
            ->setName(ActionEnum::COFFEE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($actionEntity);

        $equipmentCoffee = new EquipmentConfig();
        $equipmentCoffee
            ->setName(GameRationEnum::COFFEE)
        ;
        $I->haveInRepository($equipmentCoffee);
        $equipment = new EquipmentConfig();
        $equipment
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setActions(new ArrayCollection([$actionEntity]))
        ;
        $I->haveInRepository($equipment);
        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setReach(ReachEnum::INVENTORY)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

        $gearMechanic = new Gear();
        $gearMechanic->setModifierConfigs(new arrayCollection([$modifierConfig]));
        $gearConfig = new ItemConfig();
        $gearConfig
            ->setName(GearItemEnum::SOAP)
            ->setMechanics(new ArrayCollection([$gearMechanic]))
        ;
        $I->haveInRepository($modifierConfig);
        $I->haveInRepository($gearMechanic);
        $I->haveInRepository($gearConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::COFFEE)
        ;
        $I->haveInRepository($statusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$statusConfig]))
            ->setEquipmentsConfig(new ArrayCollection([$equipment, $equipmentCoffee, $gearConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
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
            'movementPoint' => 0,
            'actionPoint' => 10,
            'healthPoint' => 10,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;

        $I->haveInRepository($gameEquipment);

        $I->refreshEntities($room);

        $gameGear = new GameItem($player);
        $gameGear
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($gearConfig)
        ;
        $I->haveInRepository($gameGear);

        $I->refreshEntities($player);

        $chargeStatus = new ChargeStatus($gameGear, $statusConfig);
        $chargeStatus
            ->setCharge(1)
        ;
        $I->haveInRepository($chargeStatus);

        $modifier = new Modifier($player, $modifierConfig);
        $modifier->setCharge($chargeStatus);
        $I->haveInRepository($modifier);

        $this->coffeeAction->loadParameters($actionEntity, $player, $gameEquipment);

        $I->assertEquals(1, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(1, $this->coffeeAction->getActionPointCost());
        $I->assertEquals(1, $chargeStatus->getCharge());

        $this->coffeeAction->execute();

        $I->assertEquals(0, $chargeStatus->getCharge());
        $I->assertEquals(1, $this->coffeeAction->getMovementPointCost());
        $I->assertEquals(0, $this->coffeeAction->getActionPointCost());
    }
}
