<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;

class DiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ModifierConfig $reduceMax1HealthPoint */
        $reduceMax1HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax2HealthPoint */
        $reduceMax2HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax4HealthPoint */
        $reduceMax4HealthPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_4_HEALTH_POINT);
        /** @var ModifierConfig $reduceMax1MoralPoint */
        $reduceMax1MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_1_MORAL_POINT);
        /** @var ModifierConfig $reduceMax2MoralPoint */
        $reduceMax2MoralPoint = $this->getReference(DiseaseModifierConfigFixtures::REDUCE_MAX_2_MORAL_POINT);
        /** @var ModifierConfig $cycle1HealthLost */
        $cycle1HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST);
        /** @var ModifierConfig $cycle2HealthLost */
        $cycle2HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_HEALTH_LOST);
        /** @var ModifierConfig $cycle4HealthLost */
        $cycle4HealthLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_4_HEALTH_LOST);
        /** @var ModifierConfig $cycle1MovementLost */
        $cycle1MovementLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_MOVEMENT_LOST);
        /** @var ModifierConfig $cycle1SatietyLost */
        $cycle1SatietyLost = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_SATIETY_LOST);
        /** @var ModifierConfig $cycle1SatietyIncrease */
        $cycle1SatietyIncrease = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_SATIETY_INCREASE);
        /** @var ModifierConfig $cycle1ActionLostRand10 */
        $cycle1ActionLostRand10 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_10);
        /** @var ModifierConfig $cycle1HealthLostRand10 */
        $cycle1HealthLostRand10 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_10);
        /** @var ModifierConfig $cycle1ActionLostRand16 */
        $cycle1ActionLostRand16 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16);
        /** @var ModifierConfig $cycle1ActionLostRand16FitfullSleep */
        $cycle1ActionLostRand16FitfullSleep = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_16_FITFULL_SLEEP);
        /** @var ModifierConfig $cycle1HealthLostRand16 */
        $cycle1HealthLostRand16 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_16);
        /** @var ModifierConfig $cycle1ActionLostRand20 */
        $cycle1ActionLostRand20 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_20);
        /** @var ModifierConfig $cycle1ActionLostRand30 */
        $cycle1ActionLostRand30 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_ACTION_LOST_RAND_30);
        /** @var ModifierConfig $cycle2ActionLostRand40 */
        $cycle2ActionLostRand40 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_2_ACTION_LOST_RAND_40);
        /** @var ModifierConfig $cycle1MovementLostRand50 */
        $cycle1MovementLostRand50 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_MOVEMENT_LOST_RAND_50);
        /** @var ModifierConfig $cycle1HealthLostRand50 */
        $cycle1HealthLostRand50 = $this->getReference(DiseaseModifierConfigFixtures::CYCLE_1_HEALTH_LOST_RAND_50);
        /** @var ModifierConfig $consume1ActionLoss */
        $consume1ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_1_ACTION_LOSS);
        /** @var ModifierConfig $consume2ActionLoss */
        $consume2ActionLoss = $this->getReference(DiseaseModifierConfigFixtures::CONSUME_2_ACTION_LOSS);
        /** @var ModifierConfig $moveIncreaseMovement */
        $moveIncreaseMovement = $this->getReference(DiseaseModifierConfigFixtures::MOVE_INCREASE_MOVEMENT);
        /** @var ModifierConfig $infected4HealthLoss */
        $infected4HealthLoss = $this->getReference(DiseaseModifierConfigFixtures::INFECTED_4_HEALTH_LOSS);
        /** @var ModifierConfig $takeCat6HealthLoss */
        $takeCat6HealthLoss = $this->getReference(DiseaseModifierConfigFixtures::TAKE_CAT_6_HEALTH_LOSS);
        /** @var ModifierConfig $shootAction10PercentAccuracyLost */
        $shootAction10PercentAccuracyLost = $this->getReference(DiseaseModifierConfigFixtures::SHOOT_ACTION_10_PERCENT_ACCURACY_LOST);
        /** @var ModifierConfig $increaseCycleDiseaseChances10 */
        $increaseCycleDiseaseChances10 = $this->getReference(DiseaseModifierConfigFixtures::INCREASE_CYCLE_DISEASE_CHANCES_10);

        /** @var SymptomConfig $biting */
        $biting = $this->getReference(DiseaseSymptomConfigFixtures::BITING);
        /** @var SymptomConfig $breakouts */
        $breakouts = $this->getReference(DiseaseSymptomConfigFixtures::BREAKOUTS);
        /** @var SymptomConfig $catAllergySymptom */
        $catAllergySymptom = $this->getReference(DiseaseSymptomConfigFixtures::CAT_ALLERGY_SYMPTOM);
        /** @var SymptomConfig $catSneezing */
        $catSneezing = $this->getReference(DiseaseSymptomConfigFixtures::CAT_SNEEZING);
        /** @var SymptomConfig $consumeDrugVomiting */
        $consumeDrugVomiting = $this->getReference(DiseaseSymptomConfigFixtures::CONSUME_DRUG_VOMITING);
        /** @var SymptomConfig $consumeVomiting */
        $consumeVomiting = $this->getReference(DiseaseSymptomConfigFixtures::CONSUME_VOMITING);
        /** @var SymptomConfig $cycleDirtiness */
        $cycleDirtiness = $this->getReference(DiseaseSymptomConfigFixtures::CYCLE_DIRTINESS);
        /** @var SymptomConfig $cycleDirtinessRand40 */
        $cycleDirtinessRand40 = $this->getReference(DiseaseSymptomConfigFixtures::CYCLE_DIRTINESS_RAND_40);
        /** @var SymptomConfig $drooling */
        $drooling = $this->getReference(DiseaseSymptomConfigFixtures::DROOLING);
        /** @var SymptomConfig $foamingMouth */
        $foamingMouth = $this->getReference(DiseaseSymptomConfigFixtures::FOAMING_MOUTH);
        /** @var SymptomConfig $moveVomiting */
        $moveVomiting = $this->getReference(DiseaseSymptomConfigFixtures::MOVE_VOMITING);
        /** @var SymptomConfig $mushSneezing */
        $mushSneezing = $this->getReference(DiseaseSymptomConfigFixtures::MUSH_SNEEZING);

        $foodPoisoning = new DiseaseConfig();
        $foodPoisoning
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->setModifierConfigs(new ArrayCollection([$reduceMax1HealthPoint]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $consumeDrugVomiting,
                $consumeVomiting,
                $moveVomiting,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($foodPoisoning);

        $acidReflux = new DiseaseConfig();
        $acidReflux
            ->setDiseaseName(DiseaseEnum::ACID_REFLUX)
            ->setModifierConfigs(new ArrayCollection([$consume2ActionLoss]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $consumeDrugVomiting,
                $consumeVomiting,
                $moveVomiting,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($acidReflux);

        $blackBite = new DiseaseConfig();
        $blackBite
            ->setDiseaseName(DiseaseEnum::BLACK_BITE)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1ActionLostRand10,
                $infected4HealthLoss,
                ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blackBite);

        $catAllergy = new DiseaseConfig();
        $catAllergy
            ->setDiseaseName(DiseaseEnum::CAT_ALLERGY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $catAllergySymptom,
                $catSneezing,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($catAllergy);

        $cold = new DiseaseConfig();
        $cold
            ->setDiseaseName(DiseaseEnum::COLD)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand20]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($cold);

        $extremeTinnitus = new DiseaseConfig();
        $extremeTinnitus
            ->setDiseaseName(DiseaseEnum::EXTREME_TINNITUS)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand16, $reduceMax2MoralPoint]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($extremeTinnitus);

        $flu = new DiseaseConfig();
        $flu
            ->setDiseaseName(DiseaseEnum::FLU)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1HealthLostRand10,
                $cycle1ActionLostRand20,
                $reduceMax2MoralPoint,
                $reduceMax2HealthPoint,
                ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                    $consumeDrugVomiting,
                    $consumeVomiting,
                    $cycleDirtinessRand40,
                    $moveVomiting,
                ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($flu);

        $fungicInfection = new DiseaseConfig();
        $fungicInfection
            ->setDiseaseName(DiseaseEnum::FUNGIC_INFECTION)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $reduceMax2HealthPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $cycleDirtiness,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($fungicInfection);

        $gastroenteritis = new DiseaseConfig();
        $gastroenteritis
            ->setDiseaseName(DiseaseEnum::GASTROENTERIS)
            ->setModifierConfigs(new ArrayCollection([
                $consume1ActionLoss,
                $cycle1HealthLostRand16,
                $cycle1MovementLost,
                $reduceMax1HealthPoint,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $consumeDrugVomiting,
                $consumeVomiting,
                $cycleDirtiness,
                $moveVomiting,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($gastroenteritis);

        $junkbumpkinitis = new DiseaseConfig();
        $junkbumpkinitis
            ->setDiseaseName(DiseaseEnum::JUNKBUMPKINITIS)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($junkbumpkinitis);

        $migraine = new DiseaseConfig();
        $migraine
            ->setDiseaseName(DiseaseEnum::MIGRAINE)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand20]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($migraine);

        $mushAllergy = new DiseaseConfig();
        $mushAllergy
            ->setDiseaseName(DiseaseEnum::MUSH_ALLERGY)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $mushSneezing,
            ]))
            ->setModifierConfigs(new ArrayCollection([
                $infected4HealthLoss,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mushAllergy);

        $quincksOedema = new DiseaseConfig();
        $quincksOedema
            ->setDiseaseName(DiseaseEnum::QUINCKS_OEDEMA)
            ->setModifierConfigs(new ArrayCollection([
                $moveIncreaseMovement,
                $reduceMax4HealthPoint,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($quincksOedema);

        $rejuvenation = new DiseaseConfig();
        $rejuvenation
            ->setDiseaseName(DiseaseEnum::REJUVENATION)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1ActionLostRand16FitfullSleep,
                $cycle1ActionLostRand20,
                $increaseCycleDiseaseChances10,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($rejuvenation);

        $rubella = new DiseaseConfig();
        $rubella
            ->setDiseaseName(DiseaseEnum::RUBELLA)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1MovementLostRand50,
                $reduceMax1HealthPoint,
                $reduceMax1MoralPoint,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($rubella);

        $sepsis = new DiseaseConfig();
        $sepsis
            ->setDiseaseName(DiseaseEnum::SEPSIS)
            ->setModifierConfigs(new ArrayCollection([$cycle4HealthLost]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($sepsis);

        $sinusStorm = new DiseaseConfig();
        $sinusStorm
            ->setDiseaseName(DiseaseEnum::SINUS_STORM)
            ->setModifierConfigs(new ArrayCollection([$cycle1ActionLostRand30]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($sinusStorm);

        $skinInflammation = new DiseaseConfig();
        $skinInflammation
            ->setDiseaseName(DiseaseEnum::SKIN_INFLAMMATION)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $breakouts,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($skinInflammation);

        $nausea = new DiseaseConfig();
        $nausea
            ->setDiseaseName(DiseaseEnum::SLIGHT_NAUSEA)
            ->setSymptomConfigs(new SymptomConfigCollection([
                $moveVomiting,
            ]))
            ->setModifierConfigs(new ArrayCollection([
                $cycle1SatietyIncrease,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($nausea);

        $smallPox = new DiseaseConfig();
        $smallPox
            ->setDiseaseName(DiseaseEnum::SMALLPOX)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2ActionLostRand40,
                $cycle1HealthLostRand50,
                $reduceMax2HealthPoint,
                $reduceMax2MoralPoint,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($smallPox);

        $spaceRabies = new DiseaseConfig();
        $spaceRabies
            ->setDiseaseName(DiseaseEnum::SPACE_RABIES)
            ->setModifierConfigs(new ArrayCollection([
                $cycle2HealthLost,
            ]))
            ->setSymptomConfigs(new SymptomConfigCollection([
                $biting,
                $drooling,
                $foamingMouth,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($spaceRabies);

        $syphilis = new DiseaseConfig();
        $syphilis
            ->setDiseaseName(DiseaseEnum::SYPHILIS)
            ->setModifierConfigs(new ArrayCollection([
                $reduceMax2MoralPoint,
                $cycle2ActionLostRand40,
                $shootAction10PercentAccuracyLost,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($syphilis);

        $tapeworm = new DiseaseConfig();
        $tapeworm
            ->setDiseaseName(DiseaseEnum::TAPEWORM)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1SatietyLost,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($tapeworm);

        $vitaminDeficiency = new DiseaseConfig();
        $vitaminDeficiency
            ->setDiseaseName(DiseaseEnum::VITAMIN_DEFICIENCY)
            ->setModifierConfigs(new ArrayCollection([
                $cycle1ActionLostRand10,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($vitaminDeficiency);

        $gameConfig
            ->addDiseaseConfig($foodPoisoning)
            ->addDiseaseConfig($vitaminDeficiency)
            ->addDiseaseConfig($tapeworm)
            ->addDiseaseConfig($syphilis)
            ->addDiseaseConfig($spaceRabies)
            ->addDiseaseConfig($smallPox)
            ->addDiseaseConfig($nausea)
            ->addDiseaseConfig($skinInflammation)
            ->addDiseaseConfig($sinusStorm)
            ->addDiseaseConfig($sepsis)
            ->addDiseaseConfig($rubella)
            ->addDiseaseConfig($rejuvenation)
            ->addDiseaseConfig($quincksOedema)
            ->addDiseaseConfig($mushAllergy)
            ->addDiseaseConfig($migraine)
            ->addDiseaseConfig($junkbumpkinitis)
            ->addDiseaseConfig($gastroenteritis)
            ->addDiseaseConfig($fungicInfection)
            ->addDiseaseConfig($flu)
            ->addDiseaseConfig($extremeTinnitus)
            ->addDiseaseConfig($cold)
            ->addDiseaseConfig($catAllergy)
            ->addDiseaseConfig($blackBite)
            ->addDiseaseConfig($acidReflux)
        ;
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(DiseaseEnum::FOOD_POISONING, $foodPoisoning);
        $this->addReference(DiseaseEnum::VITAMIN_DEFICIENCY, $vitaminDeficiency);
        $this->addReference(DiseaseEnum::TAPEWORM, $tapeworm);
        $this->addReference(DiseaseEnum::SYPHILIS, $syphilis);
        $this->addReference(DiseaseEnum::SPACE_RABIES, $spaceRabies);
        $this->addReference(DiseaseEnum::SMALLPOX, $smallPox);
        $this->addReference(DiseaseEnum::SLIGHT_NAUSEA, $nausea);
        $this->addReference(DiseaseEnum::SKIN_INFLAMMATION, $skinInflammation);
        $this->addReference(DiseaseEnum::SINUS_STORM, $sinusStorm);
        $this->addReference(DiseaseEnum::SEPSIS, $sepsis);
        $this->addReference(DiseaseEnum::RUBELLA, $rubella);
        $this->addReference(DiseaseEnum::REJUVENATION, $rejuvenation);
        $this->addReference(DiseaseEnum::QUINCKS_OEDEMA, $quincksOedema);
        $this->addReference(DiseaseEnum::MUSH_ALLERGY, $mushAllergy);
        $this->addReference(DiseaseEnum::MIGRAINE, $migraine);
        $this->addReference(DiseaseEnum::JUNKBUMPKINITIS, $junkbumpkinitis);
        $this->addReference(DiseaseEnum::GASTROENTERIS, $gastroenteritis);
        $this->addReference(DiseaseEnum::FUNGIC_INFECTION, $fungicInfection);
        $this->addReference(DiseaseEnum::FLU, $flu);
        $this->addReference(DiseaseEnum::EXTREME_TINNITUS, $extremeTinnitus);
        $this->addReference(DiseaseEnum::COLD, $cold);
        $this->addReference(DiseaseEnum::CAT_ALLERGY, $catAllergy);
        $this->addReference(DiseaseEnum::BLACK_BITE, $blackBite);
        $this->addReference(DiseaseEnum::ACID_REFLUX, $acidReflux);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DiseaseModifierConfigFixtures::class,
            DiseaseSymptomConfigFixtures::class,
        ];
    }
}
