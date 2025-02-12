<?php

namespace Mush\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

class AbstractFunctionalTest
{
    protected Daedalus $daedalus;
    protected ArrayCollection $players;
    protected Player $player;
    protected Player $player1;
    protected Player $player2;

    public function _before(FunctionalTester $I)
    {
        $this->daedalus = $this->createDaedalus($I);
        $this->players = $this->createPlayers($I, $this->daedalus);
        $this->daedalus->setPlayers($this->players);
        $I->haveInRepository($this->daedalus);

        $this->player1 = $this->players->first();
        $this->player2 = $this->players->last();
        $this->player = $this->player1;
    }

    protected function createDaedalus(FunctionalTester $I): Daedalus
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(0)
            ->setDaedalusVariables($daedalusConfig)
            ->setCycleStartedAt(new \DateTime())
        ;

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $neron = new Neron();
        $I->haveInRepository($neron);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName(Uuid::v4()->toRfc4122())
            ->setNeron($neron)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH)
        ;
        $I->haveInRepository($mushChannel);

        $I->refreshEntities($daedalusInfo);

        $places = $this->createPlaces($I, $daedalus);
        $daedalus->setPlaces($places);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $I->haveInRepository($daedalus);

        return $daedalus;
    }

    protected function createPlayers(FunctionalTester $I, Daedalus $daedalus): Collection
    {
        $players = new ArrayCollection([]);
        $chunCharacterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['characterName' => CharacterEnum::CHUN]);
        $kuanTiCharacterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['characterName' => CharacterEnum::KUAN_TI]);

        $characterConfigs = [$chunCharacterConfig, $kuanTiCharacterConfig];

        foreach ($characterConfigs as $characterConfig) {
            $player = new Player();

            $user = new User();
            $user
                ->setUserId('user' . Uuid::v4()->toRfc4122())
                ->setUserName('user' . Uuid::v4()->toRfc4122())
            ;
            $I->haveInRepository($user);

            $playerInfo = new PlayerInfo($player, $user, $characterConfig);
            $I->haveInRepository($playerInfo);

            $player->setDaedalus($this->daedalus);
            $player->setPlace($daedalus->getPlaceByName(RoomEnum::LABORATORY));
            $player->setPlayerVariables($characterConfig);

            $I->haveInRepository($player);

            $players->add($player);
        }

        return $players;
    }

    protected function createPlaces(FunctionalTester $I, Daedalus $daedalus): ArrayCollection
    {
        /** @var PlaceConfig $laboratoryConfig */
        $laboratoryConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::LABORATORY]);
        $laboratory = new Place();
        $laboratory
            ->setName(RoomEnum::LABORATORY)
            ->setType($laboratoryConfig->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($laboratory);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($space);

        return new ArrayCollection([$laboratory, $space]);
    }

    protected function createExtraPlace(string $placeName, FunctionalTester $I, Daedalus $daedalus): Place
    {
        /** @var PlaceConfig $extraRoomConfig */
        $extraRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => $placeName]);
        $extraRoom = new Place();
        $extraRoom
            ->setName($placeName)
            ->setType($extraRoomConfig->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($extraRoom);

        $I->haveInRepository($daedalus);

        return $extraRoom;
    }
}
