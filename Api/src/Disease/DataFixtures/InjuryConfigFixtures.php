<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;

class InjuryConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const BROKEN_FINGER = 'broken_finger';
    public const BROKEN_FOOT = 'broken_foot';
    public const BROKEN_LEG = 'broken_leg';
    public const BROKEN_RIBS = 'broken_ribs';
    public const BRUISED_SHOULDER = 'bruised_shoulder';
    public const BURNS_50_OF_BODY = 'burns_50_of_body';
    public const BURNS_90_OF_BODY = 'burns_90_of_body';
    public const BURNT_ARMS = 'burnt_arms';
    public const BURNT_HAND = 'burnt_hand';
    public const BURST_NOSE = 'burst_nose';
    public const BUSTED_ARM_JOINT = 'busted_arm_joint';
    public const BUSTED_SHOULDER = 'busted_shoulder';
    public const CRITICAL_HAEMORRHAGE = 'critical_haemorrhage';
    public const DAMAGED_EARS = 'damaged_ears';
    public const DESTROYED_EARS = 'destroyed_ears';
    public const DISFUNCTIONAL_LIVER = 'disfunctional_liver';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ModifierConfig $consume2ActionLoss */
        $consume2ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_2_ACTION_LOSS);
        /** @var ModifierConfig $cycle2HealthLoss */
        $cycle2HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_HEALTH_LOST);
        /** @var ModifierConfig $dirtyAllHealthLoss */
        $dirtyAllHealthLoss = $this->getReference(InjuryModifierConfigFixtures::DIRTY_ALL_HEALTH_LOSS);
        /** @var ModifierConfig $increaseCycleDiseaseChances10 */
        $increaseCycleDiseaseChances10 = $this->getReference(DiseaseModifierConfigFixtures::INCREASE_CYCLE_DISEASE_CHANCES_10);
        /** @var ModifierConfig $moveIncreaseMovement */
        $moveIncreaseMovement = $this->getReference(DiseaseModifierConfigFixtures::MOVE_INCREASE_MOVEMENT);
        /** @var ModifierConfig $notMoveAction1Increase */
        $notMoveAction1Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_1_INCREASE);
        /** @var ModifierConfig $notMoveAction2Increase */
        $notMoveAction2Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_2_INCREASE);
        /** @var ModifierConfig $reduceMax1HealthPoint */
        $reduceMax1HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax1MoralPoint */
        $reduceMax1MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_MORAL_POINT);
        /** @var ModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);
        /** @var ModifierConfig $reduce5MaxMovementPoint */
        $reduceMax5MovementPoint = $this->getReference(InjuryModifierConfigFixtures::REDUCE_MAX_5_MOVEMENT_POINT);
        /** @var ModifierConfig $shootAction10PercentAccuracyLost */
        $shootAction10PercentAccuracyLost = $this->getReference(DiseaseModifierConfigFixtures::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST);
        /** @var ModifierConfig $shootAction20PercentAccuracyLost */
        $shootAction20PercentAccuracyLost = $this->getReference(InjuryModifierConfigFixtures::SHOOT_ACTION_20_PERCENT_ACCURACY_LOST);
        /** @var ModifierConfig $shootAction40PercentAccuracyLost */
        $shootAction40PercentAccuracyLost = $this->getReference(InjuryModifierConfigFixtures::SHOOT_ACTION_40_PERCENT_ACCURACY_LOST);

        /** @var SymptomConfig $cantPickUpHeavyItems */
        $cantPickUpHeavyItems = $this->getReference(InjurySymptomConfigFixtures::CANT_PICK_UP_HEAVY_ITEMS);
        /** @var SymptomConfig $consumeVomiting */
        $consumeVomiting = $this->getReference(DiseaseSymptomConfigFixtures::CONSUME_VOMITING);
        /** @var SymptomConfig $deaf */
        $deaf = $this->getReference(InjurySymptomConfigFixtures::DEAF);
        /** @var SymptomConfig $drooling */
        $drooling = $this->getReference(DiseaseSymptomConfigFixtures::DROOLING);
        /** @var SymptomConfig $moveVomiting */
        $moveVomiting = $this->getReference(DiseaseSymptomConfigFixtures::MOVE_VOMITING);

        $brokenFinger = new DiseaseConfig();
        $brokenFinger
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BROKEN_FINGER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
            ]))
        ;
        $manager->persist($brokenFinger);

        $brokenFoot = new DiseaseConfig();
        $brokenFoot
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BROKEN_FOOT)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $moveIncreaseMovement,
            ]))
        ;
        $manager->persist($brokenFoot);

        $brokenLeg = new DiseaseConfig();
        $brokenLeg
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BROKEN_LEG)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax5MovementPoint,
                $moveIncreaseMovement,
            ]))
        ;
        $manager->persist($brokenLeg);

        $brokenRibs = new DiseaseConfig();
        $brokenRibs
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BROKEN_RIBS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $shootAction20PercentAccuracyLost,
            ]))
        ;
        $manager->persist($brokenRibs);

        $bruisedShoulder = new DiseaseConfig();
        $bruisedShoulder
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BRUISED_SHOULDER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax1HealthPoint,
                $shootAction10PercentAccuracyLost,
            ]))
        ;
        $manager->persist($bruisedShoulder);

        $burns50OfBody = new DiseaseConfig();
        $burns50OfBody
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURNS_50_OF_BODY)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $increaseCycleDiseaseChances10,
                ]))
        ;
        $manager->persist($burns50OfBody);

        $burns90OfBody = new DiseaseConfig();
        $burns90OfBody
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURNS_90_OF_BODY)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $increaseCycleDiseaseChances10,
                $dirtyAllHealthLoss,
                ]))
        ;
        $manager->persist($burns90OfBody);

        $burntArms = new DiseaseConfig();
        $burntArms
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURNT_ARMS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction2Increase,
                $increaseCycleDiseaseChances10,
                $shootAction20PercentAccuracyLost,
            ]))
        ;
        $manager->persist($burntArms);

        $burntHand = new DiseaseConfig();
        $burntHand
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURNT_HAND)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $reduceMax1HealthPoint,
            ]))
        ;
        $manager->persist($burntHand);

        $burstNose = new DiseaseConfig();
        $burstNose
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURST_NOSE)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
            ]))
        ;
        $manager->persist($burstNose);

        $bustedArmJoint = new DiseaseConfig();
        $bustedArmJoint
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BUSTED_ARM_JOINT)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction2Increase,
                $shootAction40PercentAccuracyLost,
            ]))
        ;
        $manager->persist($bustedArmJoint);

        $bustedShoulder = new DiseaseConfig();
        $bustedShoulder
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BUSTED_SHOULDER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $shootAction40PercentAccuracyLost,
                $notMoveAction2Increase,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $cantPickUpHeavyItems,
            ]))
        ;
        $manager->persist($bustedShoulder);

        $criticalHaemorrhage = new DiseaseConfig();
        $criticalHaemorrhage
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::CRITICAL_HAEMORRHAGE)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2HealthLost,
            ]))
        ;
        $manager->persist($criticalHaemorrhage);

        $damagedEars = new DiseaseConfig();
        $damagedEars
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::DAMAGED_EARS)
            ->setType(TypeEnum::INJURY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $deaf,
            ]))
        ;
        $manager->persist($damagedEars);

        $destroyedEars = new DiseaseConfig();
        $destroyedEars
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::DESTROYED_EARS)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax1MoralPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $deaf,
            ]))
        ;
        $manager->persist($destroyedEars);

        $disfunctionalLiver = new DiseaseConfig();
        $disfunctionalLiver
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::DISFUNCTIONAL_LIVER)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $consume2ActionLoss,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $consumeVomiting,
                $drooling,
                $moveVomiting,
            ]))
        ;
        $manager->persist($disfunctionalLiver);

        $manager->flush();

        $this->addReference(self::BROKEN_FINGER, $brokenFinger);
        $this->addReference(self::BROKEN_FOOT, $brokenFoot);
        $this->addReference(self::BROKEN_LEG, $brokenLeg);
        $this->addReference(self::BROKEN_RIBS, $brokenRibs);
        $this->addReference(self::BRUISED_SHOULDER, $bruisedShoulder);
        $this->addReference(self::BURNS_50_OF_BODY, $burns50OfBody);
        $this->addReference(self::BURNS_90_OF_BODY, $burns90OfBody);
        $this->addReference(self::BURNT_ARMS, $burntArms);
        $this->addReference(self::BURNT_HAND, $burntHand);
        $this->addReference(self::BURST_NOSE, $burstNose);
        $this->addReference(self::BUSTED_ARM_JOINT, $bustedArmJoint);
        $this->addReference(self::BUSTED_SHOULDER, $bustedShoulder);
        $this->addReference(self::CRITICAL_HAEMORRHAGE, $criticalHaemorrhage);
        $this->addReference(self::DAMAGED_EARS, $damagedEars);
        $this->addReference(self::DESTROYED_EARS, $destroyedEars);
        $this->addReference(self::DISFUNCTIONAL_LIVER, $disfunctionalLiver);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
            DiseaseSymptomConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
            InjurySymptomConfigFixtures::class,
        ];
    }
}
