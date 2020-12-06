<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Dismountable;
use Mush\Equipment\Enum\ItemEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $dismountableType1 = new Dismountable();
        $dismountableType1
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;

        $camera = new ItemConfig();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$dismountableType1]))
        ;
        $manager->persist($camera);
        $manager->persist($dismountableType1);

        $mycoAlarm = new ItemConfig();
        $mycoAlarm
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MYCO_ALARM)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$dismountableType1]))
        ;
        $manager->persist($mycoAlarm);

        $dismountableType2 = new Dismountable();
        $dismountableType2
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;
        $tabulatrix = new ItemConfig();
        $tabulatrix
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TABULATRIX)
            ->setIsHeavy(false)
            ->setIsTakeable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dismountableType2]))
        ;
        $manager->persist($tabulatrix);
        $manager->persist($dismountableType2);

        $plasticScraps = new ItemConfig();
        $plasticScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($plasticScraps);

        $oldTShirt = new ItemConfig();
        $oldTShirt
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_T_SHIRT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($oldTShirt);

        $dismountableType3 = new Dismountable();
        $dismountableType3
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(50)
        ;

        $thickTube = new ItemConfig();
        $thickTube
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THICK_TUBE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$dismountableType3]))
        ;
        $manager->persist($thickTube);
        $manager->persist($dismountableType3);

        $dismountableType4 = new Dismountable();
        $dismountableType4
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(50)
        ;

        $mushDisk = new ItemConfig();
        $mushDisk
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MUSH_GENOME_DISK)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$dismountableType4]))
        ;
        $manager->persist($mushDisk);
        $manager->persist($dismountableType4);

        $mushSample = new ItemConfig();
        $mushSample
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MUSH_SAMPLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($mushSample);

        $starmapFragment = new ItemConfig();
        $starmapFragment
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STARMAP_FRAGMENT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($starmapFragment);

        $waterStick = new ItemConfig();
        $waterStick
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WATER_STICK)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($waterStick);

        $hydropot = new ItemConfig();
        $hydropot
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::HYDROPOT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($hydropot);

        //@TODO add drones, cat, coffee thermos, lunchbox, survival kit, oxygen capsule, fuel capsule
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
