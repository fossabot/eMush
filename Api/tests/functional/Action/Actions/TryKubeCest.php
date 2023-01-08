<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\TryKube;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\User\Entity\User;

class TryKubeCest
{
    private TryKube $tryKube;

    public function _before(FunctionalTester $I)
    {
        $this->tryKube = $I->grabService(TryKube::class);
    }

    public function testTryKube(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::TRY_KUBE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
           ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);

        $itemConfig
            ->setEquipmentName(ToolItemEnum::MAD_KUBE)
            ->setActions(new ArrayCollection([$action]))
        ;

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ToolItemEnum::MAD_KUBE)
            ->setEquipment($itemConfig)
        ;
        $I->haveInRepository($gameItem);

        $this->tryKube->loadParameters($action, $player, $gameItem);

        $I->assertTrue($this->tryKube->isVisible());
        $I->assertNull($this->tryKube->cannotExecuteReason());

        $this->tryKube->execute();

        $I->assertEquals(9, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::TRY_KUBE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
