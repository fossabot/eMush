<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;

class DifficultyConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $difficultyConfig = new DifficultyConfig();

        $difficultyConfig
            ->setEquipmentBreakRate(30)
            ->setDoorBreakRate(40)
            ->setEquipmentFireBreakRate(30)
            ->setStartingFireRate(2)
            ->setPropagatingFireRate(30)
            ->setHullFireDamageRate(20)
            ->setTremorRate(5)
            ->setMetalPlateRate(5)
            ->setElectricArcRate(5)
            ->setPanicCrisisRate(5)
            ->setFireHullDamage([2 => 1, 4 => 1])
            ->setFirePlayerDamage([2 => 1])
            ->setElectricArcPlayerDamage([3 => 1])
            ->setTremorPlayerDamage([1 => 1, 2 => 1, 3 => 1])
            ->setMetalPlatePlayerDamage([4 => 1, 5 => 1, 6 => 1])
            ->setPanicCrisisPlayerDamage([3 => 1])
            ->setPlantDiseaseRate(5)
            ->setCycleDiseaseRate(20)
        ;

        $manager->persist($difficultyConfig);

        $gameConfig->setDifficultyConfig($difficultyConfig);
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
