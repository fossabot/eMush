<?php

namespace functional\Daedalus\Service;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;

class CreateDaedalusCest
{
    private DaedalusService $daedalusService;

    public function _before(FunctionalTester $I)
    {
        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function createDaedalusTest(FunctionalTester $I)
    {
        // Lets create a Daedalus with 3 rooms, few random equipment.
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        // roomConfigs
        $placeConfig1 = new PlaceConfig();
        $placeConfig1
            ->setName(RoomEnum::FRONT_CORRIDOR)
            ->setDoors([DoorEnum::FRONT_CORRIDOR_CENTRAL_CORRIDOR])
        ;
        $placeConfig2 = new PlaceConfig();
        $placeConfig2
            ->setName(RoomEnum::CENTRAL_CORRIDOR)
            ->setDoors([DoorEnum::FRONT_CORRIDOR_CENTRAL_CORRIDOR, DoorEnum::REFECTORY_CENTRAL_CORRIDOR])
            ->setItems([ItemEnum::HYDROPOT])
        ;
        $placeConfig3 = new PlaceConfig();
        $placeConfig3
            ->setName(RoomEnum::REFECTORY)
            ->setDoors([DoorEnum::REFECTORY_CENTRAL_CORRIDOR])
            ->setEquipments([EquipmentEnum::GRAVITY_SIMULATOR])
        ;
        $I->haveInRepository($placeConfig1);
        $I->haveInRepository($placeConfig2);
        $I->haveInRepository($placeConfig3);

        $daedalusConfig = $this->createDaedalusConfig(new ArrayCollection([$placeConfig1, $placeConfig2, $placeConfig3]));
        $I->haveInRepository($daedalusConfig);

        // status config
        $alienArtifact = new StatusConfig();
        $alienArtifact
            ->setName(EquipmentStatusEnum::ALIEN_ARTEFACT)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($alienArtifact);

        // Modifier configs
        $gravityModifier = new ModifierConfig();
        $gravityModifier
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope(ActionEnum::MOVE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
        ;
        $I->haveInRepository($gravityModifier);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$gravityModifier]));
        $I->haveInRepository($gear);

        // Equipment Configs
        $waterStick = new ItemConfig();
        $waterStick
            ->setName(ItemEnum::WATER_STICK)
            ->setInitStatus(new ArrayCollection([$alienArtifact]))
        ;
        $I->haveInRepository($waterStick);
        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setMechanics(new ArrayCollection([$gear]))
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
        ;
        $I->haveInRepository($gravitySimulator);
        $hydropot = new ItemConfig();
        $hydropot
            ->setName(ItemEnum::HYDROPOT)
        ;
        $I->haveInRepository($hydropot);
        $door = new EquipmentConfig();
        $door
            ->setName(EquipmentEnum::DOOR)
        ;
        $I->haveInRepository($door);

        $gameConfig = new GameConfig();
        $gameConfig
            ->setName('name')
            ->setDaedalusConfig($daedalusConfig)
            ->setEquipmentsConfig(new ArrayCollection([$door, $hydropot, $gravitySimulator, $waterStick]))
            ->setStatusConfigs(new ArrayCollection([$alienArtifact]))
        ;
        $I->haveInRepository($gameConfig);

        $daedalus = $this->daedalusService->createDaedalus($gameConfig, 'name', LanguageEnum::FRENCH);

        $I->assertEquals('name', $daedalus->getDaedalusInfo()->getName());
        $I->assertCount(3, $daedalus->getPlaces());
        $I->assertCount(1, $daedalus->getModifiers());
        $I->assertCount(0, $daedalus->getPlayers());
        $I->assertNotNull($room1 = $daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR));
        $I->assertNotNull($room2 = $daedalus->getPlaceByName(RoomEnum::CENTRAL_CORRIDOR));
        $I->assertNotNull($room3 = $daedalus->getPlaceByName(RoomEnum::REFECTORY));

        // Check doors
        $I->assertCount(1, $room1->getDoors());
        $I->assertCount(2, $room2->getDoors());
        $I->assertCount(1, $room3->getDoors());

        $I->assertNotNull($gameGravitySimulator = $room3->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR)->first()
        );

        $I->assertNotNull($gameHydropot = $room2->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === ItemEnum::HYDROPOT)->first()
        );

        $equipmentCollection = new ArrayCollection(array_merge($room1->getEquipments()->toArray(), $room2->getEquipments()->toArray()));

        $I->assertNotNull($gameWaterStick = $equipmentCollection
            ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === ItemEnum::WATER_STICK)->first()
        );

        $I->assertInstanceOf(GameItem::class, $gameWaterStick);
        $I->assertCount(1, $gameWaterStick->getStatuses());
    }

    private function createDaedalusConfig(ArrayCollection $placesConfig): DaedalusConfig
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setName('default')
            ->setInitOxygen(15)
            ->setInitFuel(25)
            ->setInitHull(80)
            ->setInitShield(-2)
            ->setDailySporeNb(4)
            ->setMaxOxygen(30)
            ->setMaxFuel(35)
            ->setMaxHull(100)
            ->setPlaceConfigs($placesConfig)
            ->setNbMush(3)
            ->setCyclePerGameDay(8)
            ->setCycleLength(60 * 3)
        ;

        $randomItemPlaces = new RandomItemPlaces();
        $randomItemPlaces
            ->setItems([
                ItemEnum::WATER_STICK,
            ])
            ->setPlaces([
                RoomEnum::CENTRAL_CORRIDOR,
                RoomEnum::FRONT_CORRIDOR,
            ])
        ;
        $daedalusConfig->setRandomItemPlace($randomItemPlaces);

        return $daedalusConfig;
    }
}
