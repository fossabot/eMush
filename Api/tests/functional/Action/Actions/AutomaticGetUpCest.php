<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\CharacterConfig;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class AutomaticGetUpCest
{
    private Shower $showerAction;

    public function _before(FunctionalTester $I)
    {
        $this->showerAction = $I->grabService(Shower::class);
    }

    public function testShower(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);


        $getUpCost = new ActionCost();
        $getUpAction = new Action();
        $getUpAction
            ->setName(ActionEnum::GET_UP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($getUpCost)
        ;
        $I->haveInRepository($getUpCost);
        $I->haveInRepository($getUpAction);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$getUpAction])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
                                            'room' => $room,
                                            'actionPoint' => 2,
                                            'healthPoint' => 6,
                                            'characterConfig' => $characterConfig,
                                        ]);

        $lyingDownStatus = new Status($player);
        $lyingDownStatus
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $actionCost = new ActionCost();

        $action = new Action();
        $action
            ->setName(ActionEnum::SHOWER)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);


        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment);



        $actionParameters = new ActionParameters();
        $actionParameters->setEquipment($gameEquipment);

        $this->showerAction->loadParameters($action, $player, $actionParameters);

        $I->assertTrue($this->showerAction->canExecute());

        $this->showerAction->execute();

        $I->assertCount(0, $player->getStatuses());
    }
}
