<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class FruitPlantConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $buildAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $consumeRationAction */
        $consumeRationAction = $this->getReference(ActionsFixtures::RATION_CONSUME);
        /** @var Action $transplantAction */
        $transplantAction = $this->getReference(ActionsFixtures::TRANSPLANT_DEFAULT);
        /** @var Action $treatAction */
        $treatAction = $this->getReference(ActionsFixtures::TREAT_PLANT);
        /** @var Action $waterAction */
        $waterAction = $this->getReference(ActionsFixtures::WATER_PLANT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);
        $plantActions = new ArrayCollection([$treatAction, $waterAction]);
        $fruitActions = new ArrayCollection([$consumeRationAction, $transplantAction]);

        $bananaMechanic = new Fruit();
        $bananaMechanic
            ->setPlantName(GamePlantEnum::BANANA_TREE)
            ->setActionPoints([1 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([1 => 1])
            ->setMoralPoints([1 => 1])
            ->setSatiety(1)
            ->setActions($fruitActions)
        ;

        $banana = new ItemConfig();
        $banana
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::BANANA)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bananaMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($bananaMechanic);
        $manager->persist($banana);

        $bananaTreeMechanic = new Plant();
        //  possibilities are stored as key, array value represent the probability to get the key value
        $bananaTreeMechanic
            ->setFruit($banana)
            ->setMaturationTime([36 => 1])
            ->setOxygen([1 => 1])
            ->setActions($plantActions)
        ;

        $bananaTree = new ItemConfig();
        $bananaTree
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BANANA_TREE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bananaTreeMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($bananaTreeMechanic);
        $manager->persist($bananaTree);

        $alienFruitPlant = [
            GameFruitEnum::CREEPNUT => GamePlantEnum::CREEPIST,
            GameFruitEnum::MEZTINE => GamePlantEnum::CACTAX,
            GameFruitEnum::GUNTIFLOP => GamePlantEnum::BIFFLON,
            GameFruitEnum::PLOSHMINA => GamePlantEnum::PULMMINAGRO,
            GameFruitEnum::PRECATI => GamePlantEnum::PRECATUS,
            GameFruitEnum::BOTTINE => GamePlantEnum::BUTTALIEN,
            GameFruitEnum::FRAGILANE => GamePlantEnum::PLATACIA,
            GameFruitEnum::ANEMOLE => GamePlantEnum::TUBILISCUS,
            GameFruitEnum::PENICRAFT => GamePlantEnum::GRAAPSHOOT,
            GameFruitEnum::KUBINUS => GamePlantEnum::FIBONICCUS,
            GameFruitEnum::CALEBOOT => GamePlantEnum::MYCOPIA,
            GameFruitEnum::FILANDRA => GamePlantEnum::ASPERAGUNK,
        ];

        foreach ($alienFruitPlant as $fruitName => $plantName) {
            $alienFruitMechanic = new Fruit();
            $alienFruitMechanic
                ->setPlantName($plantName)
                ->setActionPoints([1 => 90, 2 => 9, 3 => 1])
                ->setMoralPoints([0 => 30, 1 => 70])
                ->setExtraEffects([ExtraEffectEnum::EXTRA_PA_GAIN => 50])
                ->setSatiety(1)
                ->setActions($fruitActions)
            ;
            $manager->persist($alienFruitMechanic);

            $alienFruit = new ItemConfig();
            $alienFruit
                ->setGameConfig($gameConfig)
                ->setName($fruitName)
                ->setIsStackable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$alienFruitMechanic]))
                ->setActions($actions)
            ;
            $manager->persist($alienFruit);

            $alienPlantMechanic = new Plant();
            $alienPlantMechanic
                ->setFruit($alienFruit)
                ->setMaturationTime([2 => 7, 4 => 7, 8 => 24, 12 => 14, 16 => 7, 24 => 7, 48 => 7])
                ->setOxygen([1 => 1])
                ->setActions($plantActions)
            ;

            $alienPlant = new ItemConfig();
            $alienPlant
                ->setGameConfig($gameConfig)
                ->setName($plantName)
                ->setIsStackable(false)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$alienPlantMechanic]))
                ->setActions($actions)
            ;
            $manager->persist($alienPlantMechanic);
            $manager->persist($alienPlant);
        }

        $junkinMechanic = new Fruit();
        $junkinMechanic
            ->setPlantName(GamePlantEnum::BUMPJUNKIN)
            ->setActionPoints([3])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])
            ->setActions($fruitActions)
        ;

        $junkin = new ItemConfig();
        $junkin
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::JUNKIN)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$junkinMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($junkinMechanic);
        $manager->persist($junkin);

        $bumpjunkinMechanic = new Plant();
        $bumpjunkinMechanic
            ->setFruit($junkin)
            ->setMaturationTime([8 => 1])
            ->setOxygen([1 => 1])
            ->setActions($plantActions)
        ;

        $bumpjunkin = new ItemConfig();
        $bumpjunkin
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BUMPJUNKIN)
            ->setIsStackable(false)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bumpjunkinMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($bumpjunkinMechanic);
        $manager->persist($bumpjunkin);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
