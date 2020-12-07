<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Dismountable;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class ExplorationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $compass = new ItemConfig();
        $compass
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::QUADRIMETRIC_COMPASS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($compass);

        $rope = new ItemConfig();
        $rope
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROPE)
            ->setIsHeavy(false)
            ->setIsDropable(false)
            ->setIsTakeable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($rope);

        $dismountableMechanic = new Dismountable();
        $dismountableMechanic
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(50)
        ;

        $drill = new ItemConfig();
        $drill
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DRILL)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(50)
            ->setMechanics(new ArrayCollection([$dismountableMechanic]))
        ;
        $manager->persist($drill);
        $manager->persist($dismountableMechanic);

        $babelModule = new ItemConfig();
        $babelModule
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BABEL_MODULE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($babelModule);

        $echolocator = new ItemConfig();
        $echolocator
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ECHOLOCATOR)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($echolocator);

        $thermosensor = new ItemConfig();
        $thermosensor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THERMOSENSOR)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$dismountableMechanic]))
        ;
        $manager->persist($thermosensor);

        $whiteFlag = new ItemConfig();
        $whiteFlag
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WHITE_FLAG)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($whiteFlag);

        $manager->flush();

        $this->addReference(ItemEnum::ECHOLOCATOR, $echolocator);
        $this->addReference(ItemEnum::WHITE_FLAG, $whiteFlag);
        $this->addReference(ItemEnum::THERMOSENSOR, $thermosensor);
        $this->addReference(ItemEnum::BABEL_MODULE, $babelModule);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
