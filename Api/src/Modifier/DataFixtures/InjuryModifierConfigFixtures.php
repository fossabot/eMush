<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class InjuryModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const DIRTY_ALL_HEALTH_LOSS = 'dirty_all_health_loss';
    public const NOT_MOVE_ACTION_1_INCREASE = 'not_move_action_1_increase';
    public const NOT_MOVE_ACTION_2_INCREASE = 'not_move_action_2_increase';
    public const NOT_MOVE_ACTION_3_INCREASE = 'not_move_action_3_increase';
    public const REDUCE_MAX_3_MOVEMENT_POINT = 'reduce_max_3_movement_point';
    public const REDUCE_MAX_5_MOVEMENT_POINT = 'reduce_max_5_movement_point';
    public const REDUCE_MAX_12_MOVEMENT_POINT = 'reduce_max_12_movement_point';
    public const SHOOT_ACTION_15_PERCENT_ACCURACY_LOST = 'shoot_action_15_percent_accuracy_lost';
    public const SHOOT_ACTION_20_PERCENT_ACCURACY_LOST = 'shoot_action_20_percent_accuracy_lost';
    public const SHOOT_ACTION_40_PERCENT_ACCURACY_LOST = 'shoot_action_40_percent_accuracy_lost';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $dirtyStatusCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_STATUS);
        $dirtyStatusCondition->setCondition(PlayerStatusEnum::DIRTY);
        $manager->persist($dirtyStatusCondition);

        $notMoveActionCondition = new ModifierCondition(ModifierConditionEnum::NOT_REASON);
        $notMoveActionCondition->setCondition(ActionEnum::MOVE);
        $manager->persist($notMoveActionCondition);

        $dirtyAllHealthLoss = new ModifierConfig();
        $dirtyAllHealthLoss
            ->setScope(StatusEvent::STATUS_APPLIED)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-999)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($dirtyStatusCondition)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($dirtyAllHealthLoss);

        $notMoveAction1Increase = new ModifierConfig();
        $notMoveAction1Increase
            ->setScope(ModifierScopeEnum::ACTIONS)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($notMoveActionCondition)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($notMoveAction1Increase);

        $notMoveAction2Increase = new ModifierConfig();
        $notMoveAction2Increase
            ->setScope(ModifierScopeEnum::ACTIONS)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($notMoveActionCondition)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($notMoveAction2Increase);

        $notMoveAction3Increase = new ModifierConfig();
        $notMoveAction3Increase
            ->setScope(ModifierScopeEnum::ACTIONS)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(3)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($notMoveActionCondition)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($notMoveAction3Increase);

        $reduceMax3MovementPoint = new ModifierConfig();
        $reduceMax3MovementPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-3)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($reduceMax3MovementPoint);

        $reduceMax5MovementPoint = new ModifierConfig();
        $reduceMax5MovementPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-5)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($reduceMax5MovementPoint);

        $reduceMax12MovementPoint = new ModifierConfig();
        $reduceMax12MovementPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-12)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($reduceMax12MovementPoint);

        $shootAction15PercentAccuracyLost = new ModifierConfig();
        $shootAction15PercentAccuracyLost
            ->setScope(ActionTypeEnum::ACTION_SHOOT)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.85)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($shootAction15PercentAccuracyLost);

        $shootAction20PercentAccuracyLost = new ModifierConfig();
        $shootAction20PercentAccuracyLost
            ->setScope(ActionTypeEnum::ACTION_SHOOT)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.80)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($shootAction20PercentAccuracyLost);

        $shootAction40PercentAccuracyLost = new ModifierConfig();
        $shootAction40PercentAccuracyLost
            ->setScope(ActionTypeEnum::ACTION_SHOOT)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.60)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($shootAction40PercentAccuracyLost);

        $manager->flush();

        $this->addReference(self::DIRTY_ALL_HEALTH_LOSS, $dirtyAllHealthLoss);
        $this->addReference(self::NOT_MOVE_ACTION_1_INCREASE, $notMoveAction1Increase);
        $this->addReference(self::NOT_MOVE_ACTION_2_INCREASE, $notMoveAction2Increase);
        $this->addReference(self::NOT_MOVE_ACTION_3_INCREASE, $notMoveAction3Increase);
        $this->addReference(self::REDUCE_MAX_3_MOVEMENT_POINT, $reduceMax3MovementPoint);
        $this->addReference(self::REDUCE_MAX_5_MOVEMENT_POINT, $reduceMax5MovementPoint);
        $this->addReference(self::REDUCE_MAX_12_MOVEMENT_POINT, $reduceMax12MovementPoint);
        $this->addReference(self::SHOOT_ACTION_15_PERCENT_ACCURACY_LOST, $shootAction15PercentAccuracyLost);
        $this->addReference(self::SHOOT_ACTION_20_PERCENT_ACCURACY_LOST, $shootAction20PercentAccuracyLost);
        $this->addReference(self::SHOOT_ACTION_40_PERCENT_ACCURACY_LOST, $shootAction40PercentAccuracyLost);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
