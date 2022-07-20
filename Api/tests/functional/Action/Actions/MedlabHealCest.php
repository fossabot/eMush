<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\MedlabHeal;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MedlabHealCest
{
    private MedlabHeal $MedlabHealAction;

    public function _before(FunctionalTester $I)
    {
        $this->MedlabHealAction = $I->grabService(MedlabHeal::class);
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testMedlabHeal(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::MEDLAB_HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
            'actionPoint' => 2,
            'characterConfig' => $characterConfig,
        ]);

        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healedPlayer */
        $healedPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->MedlabHealAction->loadParameters($action, $healerPlayer, $healedPlayer);

        $I->assertTrue($this->MedlabHealAction->isVisible());
        $I->assertNull($this->MedlabHealAction->cannotExecuteReason());

        $this->MedlabHealAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healedPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getId(),
            'player' => $healerPlayer->getId(),
            'log' => ActionLogEnum::HEAL_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testMedlabHealOutsideMedlab(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $laboratory */
        $laboratory = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::LABORATORY]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::MEDLAB_HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
            'actionPoint' => 2,
            'characterConfig' => $characterConfig,
        ]);

        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healedPlayer */
        $healedPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->MedlabHealAction->loadParameters($action, $healerPlayer, $healedPlayer);

        $I->assertFalse($this->MedlabHealAction->isVisible());
    }
}
