<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerModifierEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDispatchMoralChange(FunctionalTester $I)
    {
        $suicidalStatusConfig = new StatusConfig();
        $suicidalStatusConfig
            ->setName(PlayerStatusEnum::SUICIDAL)
        ;
        $demoralizedStatusConfig = new StatusConfig();
        $demoralizedStatusConfig
            ->setName(PlayerStatusEnum::DEMORALIZED)
        ;
        $I->haveInRepository($suicidalStatusConfig);
        $I->haveInRepository($demoralizedStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$demoralizedStatusConfig, $suicidalStatusConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'moralPoint' => 5,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -1,
            EventEnum::PLAYER_DEATH,
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(4, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());

        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(3, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->seeInRepository(
            Status::class, [
            'statusConfig' => $demoralizedStatusConfig->getId(),
        ]);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -2,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(1, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->dontSeeInRepository(
            Status::class, [
            'statusConfig' => $demoralizedStatusConfig->getId(),
        ]);
        $I->seeInRepository(
            Status::class, [
            'statusConfig' => $suicidalStatusConfig->getId(),
        ]);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -1,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(0, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->dontSeeInRepository(
            Status::class, [
            'statusConfig' => $demoralizedStatusConfig->getId(),
        ]);
        $I->seeInRepository(
            Status::class, [
            'statusConfig' => $suicidalStatusConfig->getId(),
        ]);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            7,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(7, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());
    }

    public function testDispatchSatietyChange(FunctionalTester $I)
    {
        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setName(PlayerStatusEnum::FULL_STOMACH)
        ;
        $starvingStatusConfig = new StatusConfig();
        $starvingStatusConfig
            ->setName(PlayerStatusEnum::STARVING)
        ;
        $I->haveInRepository($fullStatusConfig);
        $I->haveInRepository($starvingStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$starvingStatusConfig, $fullStatusConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'satiety' => 0,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -1,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            2,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            1,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(3, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -1,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -27,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }

    public function testDispatchMushSatietyChange(FunctionalTester $I)
    {
        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setName(PlayerStatusEnum::FULL_STOMACH)
        ;
        $I->haveInRepository($fullStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$fullStatusConfig])]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'satiety' => 0,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -1,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            4,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            -29,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }
}
