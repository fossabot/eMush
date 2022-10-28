<?php

namespace Mush\Disease\Service;

use DateTime;
use Mush\Action\Actions\Attack;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Shoot;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SymptomService implements SymptomServiceInterface
{
    private ActionServiceInterface $actionService;
    private EventDispatcherInterface $eventDispatcher;
    private ModifierServiceInterface $modifierService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private ValidatorInterface $validator;

    public function __construct(
        ActionServiceInterface $actionService,
        EventDispatcherInterface $eventDispatcher,
        ModifierServiceInterface $modifierService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        ValidatorInterface $validator,
    ) {
        $this->actionService = $actionService;
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->validator = $validator;
    }

    public function handleCycleSymptom(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        switch ($symptomConfig->getName()) {
            case SymptomEnum::BITING:
                $this->handleBiting($symptomConfig, $player, $time);
                break;
            case SymptomEnum::DIRTINESS:
                $this->handleDirtiness($symptomConfig, $player, $time);
                break;
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($symptomConfig, $player, $time);
                break;
            default:
                throw new \Exception('Unknown cycle change symptom');
        }
    }

    public function handleStatusAppliedSymptom(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        switch ($symptomConfig->getName()) {
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($symptomConfig, $player, $time);
                break;
            default:
                throw new \Exception('Unknown status applied symptom');
        }
    }

    private function handleBiting(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        $playerToBite = $this->randomService->getRandomPlayer($victims);

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['target_character'] = $playerToBite->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $playerModifierEvent = new PlayerVariableEvent(
            $playerToBite,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            $symptomConfig->getName(),
            $time
        );

        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function handleBreakouts(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::BREAKOUTS) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handleCatAllergy(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::CAT_ALLERGY) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['character_gender'] = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $damageEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -6,
            $symptomConfig->getName(),
            $time
        );

        $this->eventDispatcher->dispatch($damageEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, $symptomConfig->getName());

        $diseaseEvent = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::PRIVATE,
            $symptomConfig->getName(),
            $time
        );

        $this->eventDispatcher->dispatch($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }

    private function handleDirtiness(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, $symptomConfig->getName(), $time);
        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleDirty(Player $player, string $reason, DateTime $time): void
    {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DIRTY,
            $player,
            $reason,
            $time
        );

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    public function handleDrooling(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::DROOLING) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handleFearOfCats(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::FEAR_OF_CATS) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $this->makePlayerRandomlyMoving($player);

        $this->createSymptomLog($symptomConfig->getName() . '_notif', $player, $time, VisibilityEnum::PRIVATE, $logParameters);
    }

    public function handleFoamingMouth(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::FOAMING_MOUTH) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handlePsychoticAttacks(SymptomConfig $symptomConfig, Player $player): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::PSYCHOTIC_ATTACKS) {
            return;
        }

        $this->makePlayerRandomlyAttacking($player);
        $this->makePlayerRandomlyShooting($player);
    }

    public function handleSneezing(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::SNEEZING) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handleSepticemia(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::SEPTICEMIA) {
            return;
        }

        if (!$player->isAlive()) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $player,
            EndCauseEnum::INFECTION,
            $time
        );

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
    }

    public function handleVomiting(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        if ($symptomConfig->getName() !== SymptomEnum::VOMITING) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, $symptomConfig->getName(), $time);
        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function createSymptomLog(string $symptomLogKey,
        Player $player,
        DateTime $date,
        string $visibility = VisibilityEnum::PUBLIC,
        array $logParameters = []): void
    {
        $this->roomLogService->createLog(
            $symptomLogKey,
            $player->getPlace(),
            $visibility,
            'event_log',
            $player,
            $logParameters,
            $date
        );
    }

    private function drawRandomPlayerInRoom(Player $player): ?Player
    {
        $otherPlayersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive()->filter(function (Player $p) use ($player) {
            return $p !== $player;
        })->toArray();

        if (count($otherPlayersInRoom) === 0) {
            return null;
        }

        $draw = $this->randomService->getRandomElements($otherPlayersInRoom, 1);
        $drawnPlayer = reset($draw);

        return $drawnPlayer;
    }

    private function getPlayerWeapon(Player $player, string $weapon): ?EquipmentConfig
    {
        $weapon = $player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === $weapon && $gameItem->isOperational()
        )->first();

        return $weapon ? $weapon->getEquipment() : null;
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty or if player doesn't have a knife, does nothing.
     */
    private function makePlayerRandomlyAttacking(Player $player): void
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return;
        }

        $knife = $this->getPlayerWeapon($player, ItemEnum::KNIFE);
        if ($knife === null) {
            return;
        }

        /** @var Action $attackActionEntity */
        $attackActionEntity = $knife->getActions()->filter(
            fn (Action $action) => $action->getName() === ActionEnum::ATTACK
        )->first();

        if (!$attackActionEntity instanceof Action) {
            throw new \Exception('makePlayerRandomlyAttacking() : Player ' . $player->getName() . ' should have a Attack action');
        }

        $attackAction = new Attack(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->modifierService,
            $this->playerDiseaseService
        );

        $attackAction->loadParameters($attackActionEntity, $player, $victim);
        $attackAction->execute();
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty or if player doesn't have a knife, does nothing.
     */
    private function makePlayerRandomlyShooting(Player $player): void
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return;
        }

        $blaster = $this->getPlayerWeapon($player, ItemEnum::BLASTER);
        if ($blaster === null) {
            return;
        }

        /** @var Action $shootActionEntity */
        $shootActionEntity = $blaster->getActions()->filter(
            fn (Action $action) => $action->getName() === ActionEnum::SHOOT
        )->first();

        if (!$shootActionEntity instanceof Action) {
            throw new \Exception('makePlayerRandomlyShooting() : Player' . $player->getName() . 'should have a Shoot action');
        }

        $shootAction = new Shoot(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->modifierService,
            $this->playerDiseaseService
        );

        $shootAction->loadParameters($shootActionEntity, $player, $victim);
        $shootAction->execute();
    }

    /**
     * This function takes a player as an argument, draws a random room and make them move to it.
     */
    private function makePlayerRandomlyMoving(Player $player): void
    {
        // get non broken doors
        $availaibleDoors = $player->getPlace()->getDoors()->filter(function (GameEquipment $door) {
            return !$door->isBroken();
        })->toArray();

        if (count($availaibleDoors) === 0) {
            return;
        }

        // get random door
        $selectedDoor = $this->randomService->getRandomElements($availaibleDoors, 1);
        $randomDoor = reset($selectedDoor);

        /** @var Action $moveActionEntity */
        $moveActionEntity = $randomDoor->getActions()->filter(function (Action $action) {
            return $action->getName() === ActionEnum::MOVE;
        })->first();

        $moveAction = new Move(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->playerService
        );
        $moveAction->loadParameters($moveActionEntity, $player, $randomDoor);
        $moveAction->execute();
    }
}
