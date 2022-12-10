<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\SelfHeal;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\User\Entity\User;

class SelfHealCest
{
    private SelfHeal $selfHealAction;

    public function _before(FunctionalTester $I)
    {
        $this->selfHealAction = $I->grabService(SelfHeal::class);
    }

    public function testSelfHeal(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(3)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::SELF_HEAL)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
            'actionPoint' => 3,
            'healthPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $healerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($healerPlayer);

        $this->selfHealAction->loadParameters($action, $healerPlayer);

        $I->assertTrue($this->selfHealAction->isVisible());
        $I->assertNull($this->selfHealAction->cannotExecuteReason());

        $this->selfHealAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healerPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getId(),
            'playerInfo' => $healerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
