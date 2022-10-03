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
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourcePointChangeEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

class ConsumeChargeOnActionCest
{
    private Coffee $coffeeAction;

    public function _before(FunctionalTester $I): void
    {
        $this->coffeeAction = $I->grabService(Coffee::class);
    }

    public function testToolCharge(FunctionalTester $I): void
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
            'actionPoint' => 10,
            'healthPoint' => 10,
            'characterConfig' => $characterConfig,
        ]);

        $equipmentCoffee = new EquipmentConfig();
        $equipmentCoffee
            ->setName(GameRationEnum::COFFEE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($equipmentCoffee);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
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

        $tool = new Tool();
        $tool->addAction($actionEntity);
        $I->haveInRepository($tool);

        $equipment = new EquipmentConfig();
        $equipment
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setMechanics(new ArrayCollection([$tool]))
            ->setGameConfig($gameConfig)
        ;

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;

        $I->haveInRepository($equipment);
        $I->haveInRepository($gameEquipment);

        $room->addEquipment($gameEquipment);
        $I->refreshEntities($room);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::COFFEE)
        ;
        $I->haveInRepository($statusConfig);
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

    public function testGearCharge(FunctionalTester $I): void
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
            'actionPoint' => 10,
            'healthPoint' => 10,
            'characterConfig' => $characterConfig,
        ]);

        $equipmentCoffee = new EquipmentConfig();
        $equipmentCoffee
            ->setName(GameRationEnum::COFFEE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($equipmentCoffee);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
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
            ->setGameConfig($gameConfig)
        ;

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;

        $I->haveInRepository($equipment);
        $I->haveInRepository($gameEquipment);

        $room->addEquipment($gameEquipment);
        $I->refreshEntities($room);

        $modifierConfig = new ModifierConfig(
            'a random modifier config',
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $modifierConfig
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT);
        $I->haveInRepository($modifierConfig);

        $gearMechanic = new Gear();
        $gearMechanic->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $gearConfig = new ItemConfig();
        $gearConfig
            ->setName(GearItemEnum::SOAP)
            ->setMechanics(new ArrayCollection([$gearMechanic]))
        ;
        $gameGear = new GameItem();
        $gameGear
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($gearConfig)
        ;
        $I->haveInRepository($gearMechanic);
        $I->haveInRepository($gearConfig);
        $I->haveInRepository($gameGear);

        $player->addEquipment($gameGear);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::COFFEE)
        ;
        $I->haveInRepository($statusConfig);
        $chargeStatus = new ChargeStatus($gameGear, $statusConfig);
        $chargeStatus
            ->setCharge(2)
        ;
        $I->haveInRepository($chargeStatus);

        $modifier = new Modifier($player, $modifierConfig);
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

    public function testGearMovementActionConversionCharge(FunctionalTester $I): void
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
            'movementPoint' => 0,
            'actionPoint' => 10,
            'healthPoint' => 10,
            'characterConfig' => $characterConfig,
        ]);

        $equipmentCoffee = new EquipmentConfig();
        $equipmentCoffee
            ->setName(GameRationEnum::COFFEE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($equipmentCoffee);

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

        $equipment = new EquipmentConfig();
        $equipment
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setActions(new ArrayCollection([$actionEntity]))
            ->setGameConfig($gameConfig)
        ;

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;

        $I->haveInRepository($equipment);
        $I->haveInRepository($gameEquipment);

        $room->addEquipment($gameEquipment);
        $I->refreshEntities($room);

        $modifierConfig = new ModifierConfig(
            'a random modifier config',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $modifierConfig
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN);

        $gearMechanic = new Gear();
        $gearMechanic->setModifierConfigs(new arrayCollection([$modifierConfig]));
        $gearConfig = new ItemConfig();
        $gearConfig
            ->setName(GearItemEnum::SOAP)
            ->setMechanics(new ArrayCollection([$gearMechanic]))
        ;
        $gameGear = new GameItem();
        $gameGear
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($gearConfig)
        ;
        $I->haveInRepository($modifierConfig);
        $I->haveInRepository($gearMechanic);
        $I->haveInRepository($gearConfig);
        $I->haveInRepository($gameGear);

        $player->addEquipment($gameGear);
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
