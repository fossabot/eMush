<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private const INFECTION_DISEASE_RATE = 2;
    private const INFECTION_DISEASES_INCUBATING_DELAY = 2;
    private const INFECTION_DISEASES_INCUBATING_LENGTH = 2;
    private const TRAUMA_PROBABILTY = 33;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private DiseaseCauseServiceInterface $diseaseCauseService;
    private ModifierServiceInterface $modifierService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        ModifierServiceInterface $modifierService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->diseaseCauseService = $diseaseCauseService;
        $this->modifierService = $modifierService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CYCLE_DISEASE => 'onCycleDisease',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onCycleDisease(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $diseaseRate = $this->modifierService->getEventModifiedValue(
            $player,
            [PlayerEvent::CYCLE_DISEASE],
            ModifierTargetEnum::PERCENTAGE,
            $difficultyConfig->getCycleDiseaseRate(),
            EventEnum::NEW_CYCLE,
            $event->getTime()
        );

        if ($this->randomService->isSuccessful($diseaseRate)) {
            if ($player->hasStatus(PlayerStatusEnum::DEMORALIZED) || $player->hasStatus(PlayerStatusEnum::SUICIDAL)) {
                $cause = DiseaseCauseEnum::CYCLE_LOW_MORALE;
            } else {
                $cause = DiseaseCauseEnum::CYCLE;
            }
            $this->diseaseCauseService->handleDiseaseForCause($cause, $player);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $playersInRoom = $event->getPlace()->getPlayers()->getPlayerAlive();

        foreach ($playersInRoom as $player) {
            if ($this->randomService->isSuccessful(self::TRAUMA_PROBABILTY)) {
                $characterGender = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';
                $this->roomLogService->createLog(
                    LogEnum::TRAUMA_DISEASE,
                    $event->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $player,
                    ['character_gender' => $characterGender],
                    $event->getTime()
                );
                $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::TRAUMA, $player);
            }
        }

        // remove disease of the player
        $diseases = $event->getPlayer()->getMedicalConditions();
        foreach ($diseases as $disease) {
            $this->playerDiseaseService->delete($disease);
        }
    }

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->randomService->isSuccessful(self::INFECTION_DISEASE_RATE)) {
            $this->diseaseCauseService->handleDiseaseForCause(
                DiseaseCauseEnum::INFECTION,
                $player,
                self::INFECTION_DISEASES_INCUBATING_DELAY,
                self::INFECTION_DISEASES_INCUBATING_LENGTH
            );
        }
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $characterConfig = $player->getPlayerInfo()->getCharacterConfig();
        $reason = $event->getReason();

        $initDiseases = $characterConfig->getInitDiseases();
        foreach ($initDiseases as $diseaseName) {
            $this->playerDiseaseService->createDiseaseFromName(
                $diseaseName,
                $player,
                $reason,
            );
        }
    }
}
