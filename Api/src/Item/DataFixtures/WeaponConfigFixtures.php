<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Charged;
use Mush\Item\Entity\Items\Dismountable;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Enum\ItemEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class WeaponConfigFixtures extends Fixture implements DependentFixtureInterface
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

        $chargedType = new Charged();
        $chargedType
            ->setMaxCharge(3)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        // @TODO more details are needed on the output of each weapon
        $blasterType = new Weapon();
        $blasterType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 5])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $blaster = new Item();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType1, $blasterType, $chargedType]))
        ;
        $manager->persist($dismountableType1);
        $manager->persist($chargedType);
        $manager->persist($blasterType);
        $manager->persist($blaster);

        $knifeType = new Weapon();
        $knifeType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([1 => 5])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $knife = new Item();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType1, $knifeType]))

        ;
        $manager->persist($knife);
        $manager->persist($knifeType);

        $grenadeType = new Weapon();
        $grenadeType
            ->setBaseAccuracy(100)
            ->setBaseDamageRange([0 => 10])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(3)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
            ;

        $grenade = new Item();
        $grenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$grenadeType]))

        ;
        $manager->persist($grenade);
        $manager->persist($grenadeType);

        $dismountableType2 = new Dismountable();
        $dismountableType2
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

        $natamyType = new Weapon();
        $natamyType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 12])
            ->setBaseInjuryNumber([1 => 3])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $natamy = new Item();
        $natamy
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::NATAMY_RIFLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType2, $natamyType, $chargedType]))
        ;
        $manager->persist($natamy);
        $manager->persist($natamyType);
        $manager->persist($dismountableType2);

        $dismountableType3 = new Dismountable();
        $dismountableType3
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(4)
            ->setChancesSuccess(12)
        ;

        $oldFaithfulType = new Weapon();
        $oldFaithfulType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 3])
            ->setBaseInjuryNumber([0 => 3])
            ->setExpeditionBonus(2)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $chargedType = new Charged();
        $chargedType
            ->setMaxCharge(12)
            ->setStartCharge(12)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $oldFaithful = new Item();
        $oldFaithful
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_FAITHFUL)
            ->setIsHeavy(true)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType3, $oldFaithfulType, $chargedType]))

        ;
        $manager->persist($oldFaithful);
        $manager->persist($oldFaithfulType);
        $manager->persist($dismountableType3);
        $manager->persist($chargedType);

        $dismountableType4 = new Dismountable();
        $dismountableType4
            ->setProducts([ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

        $chargedType = new Charged();
        $chargedType
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $lizaroJungleType = new Weapon();
        $lizaroJungleType
            ->setBaseAccuracy(99)
            ->setBaseDamageRange([3 => 5])
            ->setBaseInjuryNumber([1 => 2])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $lizaroJungle = new Item();
        $lizaroJungle
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LIZARO_JUNGLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType4, $lizaroJungleType, $chargedType]))

        ;
        $manager->persist($lizaroJungle);
        $manager->persist($lizaroJungleType);
        $manager->persist($dismountableType4);
        $manager->persist($chargedType);

        $rocketLauncherType = new Weapon();
        $rocketLauncherType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([0 => 8])
            ->setBaseInjuryNumber([0 => 2])
            ->setExpeditionBonus(3)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $rocketLauncher = new Item();
        $rocketLauncher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROCKET_LAUNCHER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType2, $rocketLauncherType, $chargedType]))

        ;
        $manager->persist($rocketLauncher);
        $manager->persist($rocketLauncherType);

        $this->addReference(ItemEnum::GRENADE, $grenade);
        $this->addReference(ItemEnum::OLD_FAITHFUL, $oldFaithful);
        $this->addReference(ItemEnum::LIZARO_JUNGLE, $lizaroJungle);
        $this->addReference(ItemEnum::ROCKET_LAUNCHER, $rocketLauncher);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
