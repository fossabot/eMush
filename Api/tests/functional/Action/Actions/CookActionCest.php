<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Cook;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;

class CookActionCest
{
    private Cook $cookAction;

    public function _before(FunctionalTester $I)
    {
        $this->cookAction = $I->grabService(Cook::class);
    }

    public function testCanReach(FunctionalTester $I)
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);
        $toolEquipment = $this->createEquipment('tool', $room1);

        $gameEquipment = $this->createEquipment(GameRationEnum::STANDARD_RATION, $room2);

        $cookActionEntity = new Action();
        $cookActionEntity->setName(ActionEnum::COOK);

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getConfig()->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment->getConfig()->setActions(new ArrayCollection([$cookActionEntity]));

        $this->cookAction->loadParameters($cookActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->cookAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->cookAction->isVisible());
    }

    public function testUsedTool(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $toolEquipment = $this->createEquipment('tool', $room);

        $gameEquipment = $this->createEquipment(GameRationEnum::STANDARD_RATION, $room);

        $cookActionEntity = new Action();
        $cookActionEntity->setName(ActionEnum::COOK);

        $this->cookAction->loadParameters($cookActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->cookAction->isVisible());

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getConfig()->setMechanics(new ArrayCollection([$tool]));

        $I->assertTrue($this->cookAction->isVisible());
    }

    public function testCookable(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $toolEquipment = $this->createEquipment('tool', $room);

        $gameEquipment = $this->createEquipment(GameRationEnum::STANDARD_RATION, $room);

        $cookActionEntity = new Action();
        $cookActionEntity->setName(ActionEnum::COOK);

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getConfig()->setMechanics(new ArrayCollection([$tool]));

        $this->cookAction->loadParameters($cookActionEntity, $player, $gameEquipment);

        $I->assertTrue($this->cookAction->isVisible());

        $gameEquipment->getConfig()->setName(GameRationEnum::COFFEE);

        $I->assertFalse($this->cookAction->isVisible());
    }

    private function createPlayer(Daedalus $daedalus, Place $room): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setName('character name');

        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setCharacterConfig($characterConfig)
        ;

        return $player;
    }

    private function createEquipment(string $name, Place $place): Equipment
    {
        $gameEquipment = new Equipment();
        $equipment = new EquipmentConfig();
        $equipment->setName($name);
        $gameEquipment
            ->setConfig($equipment)
            ->setHolder($place)
            ->setName($name)
        ;

        return $gameEquipment;
    }
}
