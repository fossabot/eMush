<?php

namespace functional\Player\Service;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class CreatePlayerServiceCest
{
    private PlayerService $playerService;

    public function _before(FunctionalTester $I)
    {
        $this->playerService = $I->grabService(PlayerService::class);
    }

    public function createPlayerTest(FunctionalTester $I)
    {
        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setName(PlayerStatusEnum::MUSH)
        ;
        $sporeStatusConfig = new ChargeStatusConfig();
        $sporeStatusConfig
            ->setName(PlayerStatusEnum::SPORES)
        ;
        $I->haveInRepository($mushStatusConfig);
        $I->haveInRepository($sporeStatusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'localizationConfig' => $localizationConfig,
            'statusConfigs' => new ArrayCollection([$sporeStatusConfig, $mushStatusConfig]),
        ]);

        /** @var CharacterConfig $gioeleCharacterConfig */
        $gioeleCharacterConfig = $I->have(CharacterConfig::class);
        $gioeleCharacterConfig->setInitStatuses(new ArrayCollection([$sporeStatusConfig]));
        /** @var $andieCharacterConfig $characterConfig */
        $andieCharacterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);
        $andieCharacterConfig->setInitStatuses(new ArrayCollection([$sporeStatusConfig]));

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['neron' => $neron, 'gameConfig' => $gameConfig]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        $daedalus->addPlace($room);
        $I->refreshEntities($daedalus);

        /** @var User $user */
        $user = $I->have(User::class);

        $charactersConfig = new ArrayCollection();
        $charactersConfig->add($gioeleCharacterConfig);
        $charactersConfig->add($andieCharacterConfig);

        $gameConfig->setCharactersConfig($charactersConfig);
        $daedalus->setGameConfig($gameConfig);

        $I->expectThrowable(\LogicException::class, fn () => $this->playerService->createPlayer($daedalus, $user, 'non_existent_player')
        );

        $playerGioele = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::GIOELE);

        $I->assertEquals($gioeleCharacterConfig, $playerGioele->getPlayerInfo()->getCharacterConfig());
        $I->assertEquals($gioeleCharacterConfig->getInitActionPoint(), $playerGioele->getActionPoint());

        $playerAndie = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::ANDIE);

        $I->assertEquals($andieCharacterConfig, $playerAndie->getPlayerInfo()->getCharacterConfig());
        $I->assertEquals($andieCharacterConfig->getInitActionPoint(), $playerAndie->getActionPoint());

        $I->assertTrue($playerAndie->isMush());
        $I->assertTrue($playerGioele->isMush());
        $I->assertNotNull($daedalus->getFilledAt());
    }
}
