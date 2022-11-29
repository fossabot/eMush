<?php

namespace Mush\Test\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageNormalizerTest extends TestCase
{
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    private MessageNormalizer $normalizer;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new MessageNormalizer(
            $this->translationService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNormalizePlayerMessage()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($playerInfo)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()))
        ;

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'message',
            'date' => 'translated date',
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeNeronMessage()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $neron = new Neron();
        $neron->setDaedalus($daedalus);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setNeron($neron)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                'player' => CharacterEnum::ANDIE,
                'cause' => EndCauseEnum::ABANDONED,
                'targetEquipment' => EquipmentEnum::ANTENNA,
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('message', $message->getTranslationParameters(), 'neron', LanguageEnum::FRENCH)
            ->andReturn('translatedMessage')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()))
        ;

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'translatedMessage',
            'date' => 'translated date',
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeNeronMessageWithChild()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $neron = new Neron();
        $neron->setDaedalus($daedalus);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);

        $createdAt = new \DateTime();

        $playerMessage = new Message();
        $playerMessage
            ->setAuthor($playerInfo)
            ->setMessage('message child')
            ->setCreatedAt($createdAt)
        ;

        $neronMessage = new Message();
        $neronMessage
            ->setNeron($neron)
            ->setMessage('message parent')
            ->setCreatedAt($createdAt)
            ->setChild(new ArrayCollection([$playerMessage]))
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('name' . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated player name')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message parent', [], 'neron', LanguageEnum::FRENCH)
            ->andReturn('translated message parent')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->twice()
        ;

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()))
        ;

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($neronMessage, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'translated message parent',
            'date' => 'translated date',
            'child' => [[
                'id' => null,
                'character' => ['key' => 'name', 'value' => 'translated player name'],
                'message' => 'message child',
                'date' => 'translated date',
                'child' => [],
            ]],
        ], $normalizedData);
    }

    public function testNormalizeDeafPlayerMessage()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);
        $player->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::DEAF);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($playerInfo)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with(DiseaseMessagesEnum::DEAF, [], 'disease_message', LanguageEnum::FRENCH)
            ->andReturn('...')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => '...',
            'date' => 'translated date',
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeParanoiacPlayerMessage()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($player, new User(), new CharacterConfig()))
        ;

        $otherPlayer = new Player();
        $otherPlayerInfo = new PlayerInfo($otherPlayer, new User(), $playerConfig);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($otherPlayerInfo)
            ->setMessage('modified message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                DiseaseMessagesEnum::MODIFICATION_CAUSE => SymptomEnum::PARANOIA_MESSAGES,
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'original message',
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'modified message',
            'date' => 'translated date',
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeParanoiacPlayerMessageSelf()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);
        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($playerInfo)
            ->setMessage('modified message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                DiseaseMessagesEnum::MODIFICATION_CAUSE => SymptomEnum::PARANOIA_MESSAGES,
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'original message',
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'original message',
            'date' => 'translated date',
            'child' => [],
        ], $normalizedData);
    }
}
