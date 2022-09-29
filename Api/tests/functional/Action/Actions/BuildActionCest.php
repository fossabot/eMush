<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;

class BuildActionCest
{
    private Build $buildAction;

    public function _before(FunctionalTester $I)
    {
        $this->buildAction = $I->grabService(Build::class);
    }

    public function testCanReach(FunctionalTester $I)
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);

        $buildActionEntity = new Action();
        $buildActionEntity->setName(ActionEnum::BUILD);

        $gameEquipment = $this->createEquipment('blueprint', $room2);

        $gameEquipment->getConfig()->setMechanics(new ArrayCollection([
            $this->createBlueprint(['metal_scraps' => 1], $buildActionEntity),
        ]));

        $this->buildAction->loadParameters($buildActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->buildAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->buildAction->isVisible());
    }

    public function testIsBlueprint(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = $this->createEquipment('blueprint', $room);

        $buildActionEntity = new Action();
        $buildActionEntity->setName(ActionEnum::BUILD);

        $this->buildAction->loadParameters($buildActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->buildAction->isVisible());

        $gameEquipment->getConfig()->setMechanics(new ArrayCollection([
            $this->createBlueprint(['metal_scraps' => 1], $buildActionEntity),
        ]));

        $I->assertTrue($this->buildAction->isVisible());
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

    private function createBlueprint(array $ingredients, Action $buildAction, ?EquipmentConfig $product = null): Blueprint
    {
        if ($product === null) {
            $product = new ItemConfig();
            $product->setName('product');
            $gameProduct = new Item();
            $gameProduct
                ->setConfig($product)
                ->setName('product')
            ;
        }

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients($ingredients)
            ->setEquipment($product)
            ->addAction($buildAction)
        ;

        return $blueprint;
    }
}
