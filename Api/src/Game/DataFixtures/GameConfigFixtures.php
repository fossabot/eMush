<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;

class GameConfigFixtures extends Fixture
{
    public const DEFAULT_GAME_CONFIG = 'default.game.config';

    public function load(ObjectManager $manager): void
    {
        $gameConfig = new GameConfig();

        $gameConfig
            ->setName('default')
            ->setNbMush(2)
            ->setCycleLength(1)
            ->setTimeZone('Europe/Paris')
            ->setLanguage('Fr-fr')
            ->setMaxNumberPrivateChannel(3)
            ->setInitHealthPoint(14)
            ->setMaxHealthPoint(14)
            ->setInitMoralPoint(14)
            ->setMaxMoralPoint(14)
            ->setInitSatiety(0)
            ->setInitActionPoint(8)
            ->setMaxActionPoint(12)
            ->setInitMovementPoint(12)
            ->setMaxMovementPoint(12)
            ->setMaxItemInInventory(3)
            ->setMaxOxygen(32)
            ->setMaxFuel(32)
        ;

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_GAME_CONFIG, $gameConfig);
    }
}
