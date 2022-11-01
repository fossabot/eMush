<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class RepairActionCest
{
    private Repair $repairAction;

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
    }

    public function testRepair(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0)
        ;

        $action = new Action();
        $action
            ->setName(ActionEnum::REPAIR)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(25)
            ->setActionCost($actionCost)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
        ;

        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActions(new ArrayCollection([$action]));

        $gameEquipment = new GameItem();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $this->repairAction->loadParameters($action, $player, $gameEquipment);

        $I->assertFalse($this->repairAction->isVisible());

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $I->assertEquals(25, $this->repairAction->getSuccessRate());

        $I->assertTrue($this->repairAction->isVisible());

        $modifierConfig = new ModifierConfig(
            ModifierNameEnum::WRENCH_MODIFIER,
            ModifierReachEnum::PLAYER,
            1.5,
            ModifierModeEnum::MULTIPLICATIVE
        );
        $modifierConfig
            ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [ActionOutputEnum::SUCCESS, ActionEnum::REPAIR]);

        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);

        $I->haveInRepository($modifier);
        $I->refreshEntities($player);

        $wrenchGear = new Gear();
        $wrenchGear->setModifierConfigs(new arrayCollection([$modifierConfig]));

        $wrench = new ItemConfig();
        $wrench
            ->setName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$wrenchGear]))
        ;

        $I->assertEquals(37, $this->repairAction->getSuccessRate());
    }
}
