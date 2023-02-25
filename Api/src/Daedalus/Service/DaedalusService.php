<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Repository\DaedalusInfoRepository;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Repository\LocalizationConfigRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class DaedalusService implements DaedalusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private DaedalusRepository $repository;
    private CycleServiceInterface $cycleService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private LocalizationConfigRepository $localizationConfigRepository;
    private DaedalusInfoRepository $daedalusInfoRepository;
    private DaedalusRepository $daedalusRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        DaedalusRepository $repository,
        CycleServiceInterface $cycleService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        LocalizationConfigRepository $localizationConfigRepository,
        DaedalusInfoRepository $daedalusInfoRepository,
        DaedalusRepository $daedalusRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->repository = $repository;
        $this->cycleService = $cycleService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->localizationConfigRepository = $localizationConfigRepository;
        $this->daedalusInfoRepository = $daedalusInfoRepository;
        $this->daedalusRepository = $daedalusRepository;
    }

    /**
     * @codeCoverageIgnore
     */
    public function persist(Daedalus $daedalus): Daedalus
    {
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();

        return $daedalus;
    }

    public function persistDaedalusInfo(DaedalusInfo $daedalusInfo): DaedalusInfo
    {
        $this->entityManager->persist($daedalusInfo);
        $this->entityManager->flush();

        return $daedalusInfo;
    }

    public function delete(Daedalus $daedalus): Daedalus
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusInfo->deleteDaedalus();

        $this->persistDaedalusInfo($daedalusInfo);

        $this->entityManager->remove($daedalus);
        $this->entityManager->flush();

        return $daedalus;
    }

    /**
     * @codeCoverageIgnore
     */
    public function findById(int $id): ?Daedalus
    {
        $daedalus = $this->repository->find($id);

        return $daedalus instanceof Daedalus ? $daedalus : null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection
    {
        return new DaedalusCollection();
    }

    public function findAvailableDaedalus(string $name): ?Daedalus
    {
        $daedalusInfo = $this->daedalusInfoRepository->findAvailableDaedalus($name);

        if ($daedalusInfo === null) {
            return null;
        }

        return $daedalusInfo->getDaedalus();
    }

    public function findAvailableDaedalusInLanguage(string $language): ?Daedalus
    {
        $daedalusInfo = $this->daedalusInfoRepository->findAvailableDaedalusInLanguage($language);

        if ($daedalusInfo === null) {
            return null;
        }

        return $daedalusInfo->getDaedalus();
    }

    public function findAvailableDaedalusInLanguageForUser(string $language, User $user): ?Daedalus
    {
        $daedalusInfo = $this->daedalusInfoRepository->findAvailableDaedalusInLanguageForUser($language, $user);

        if ($daedalusInfo === null) {
            return null;
        }

        return $daedalusInfo->getDaedalus();
    }

    public function existAvailableDaedalus(): bool
    {
        return $this->daedalusInfoRepository->existAvailableDaedalus();
    }

    public function existAvailableDaedalusInLanguage(string $language): bool
    {
        return $this->daedalusInfoRepository->existAvailableDaedalusInLanguage($language);
    }

    public function existAvailableDaedalusWithName(string $name): bool
    {
        return $this->daedalusInfoRepository->existAvailableDaedalusWithName($name);
    }

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection
    {
        return $daedalus->getGameConfig()->getCharactersConfig()->filter(
            fn (CharacterConfig $characterConfig) => !$daedalus->getPlayers()->exists(
                fn (int $key, Player $player) => ($player->getName() === $characterConfig->getCharacterName())
            )
        );
    }

    public function createDaedalus(GameConfig $gameConfig, string $name, string $language): Daedalus
    {
        $daedalus = new Daedalus();

        $daedalusConfig = $gameConfig->getDaedalusConfig();

        $daedalus
            ->setCycle(0)
            ->setDaedalusVariables($daedalusConfig)
        ;

        $localizationConfig = $this->localizationConfigRepository->findByLanguage($language);
        if ($localizationConfig === null) {
            throw new \Error('there is no localizationConfig for this language');
        }

        $neron = new Neron();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName($name)
            ->setNeron($neron)
        ;
        $this->persistDaedalusInfo($daedalusInfo);

        $daedalusEvent = new DaedalusInitEvent(
            $daedalus,
            $daedalusConfig,
            [EventEnum::CREATE_DAEDALUS],
            new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusInitEvent::NEW_DAEDALUS);

        return $daedalus;
    }

    public function endDaedalus(Daedalus $daedalus, string $cause, \DateTime $date): ClosedDaedalus
    {
        $this->killRemainingPlayers($daedalus, [$cause], $date);

        $daedalus->setFinishedAt(new \DateTime());

        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusInfo->setGameStatus(GameStatusEnum::FINISHED);

        $this->persistDaedalusInfo($daedalusInfo);

        // update closedDaedalus entity
        $closedDaedalus = $daedalusInfo->getClosedDaedalus();
        $closedDaedalus->updateEnd($daedalus, $cause);
        $daedalusInfo->setClosedDaedalus($closedDaedalus);
        $this->persistDaedalusInfo($daedalusInfo);

        /** @var Player $player */
        foreach ($daedalus->getPlayers() as $player) {
            /** @var ClosedPlayer $deadPlayerInfo */
            $deadPlayerInfo = $player->getPlayerInfo()->getClosedPlayer();

            $deadPlayerInfo->setClosedDaedalus($closedDaedalus);
            $closedDaedalus->addPlayer($deadPlayerInfo);

            $this->entityManager->persist($deadPlayerInfo);
            $this->entityManager->flush();
        }

        $this->entityManager->persist($closedDaedalus);
        $this->entityManager->flush();

        return $closedDaedalus;
    }

    public function closeDaedalus(Daedalus $daedalus, array $reasons, \DateTime $date): DaedalusInfo
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();

        $daedalusInfo->setGameStatus(GameStatusEnum::CLOSED);

        $daedalusEvent = new DaedalusEvent(
            $daedalus,
            $reasons,
            $date
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DELETE_DAEDALUS);

        $this->delete($daedalus);

        return $daedalusInfo;
    }

    public function startDaedalus(Daedalus $daedalus): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        $time = new \DateTime();
        $daedalus->setCreatedAt($time);
        $daedalus->setCycle($this->cycleService->getInDayCycleFromDate($time, $daedalus));
        $daedalus->setCycleStartedAt($this->cycleService->getDaedalusStartingCycleDate($daedalus));

        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);

        $this->persistDaedalusInfo($daedalusInfo);

        return $daedalus;
    }

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        // Chose alpha Mushs
        $chancesArray = [];

        foreach ($gameConfig->getCharactersConfig() as $characterConfig) {
            // @TODO lower $mushChance if user is a beginner
            // @TODO (maybe add a "I want to be mush" setting to increase this proba)

            $mushChance = 1;
            if (!$characterConfig->getInitStatuses()
                ->filter(fn (StatusConfig $statusConfig) => $statusConfig->getStatusName() === PlayerStatusEnum::IMMUNIZED)->isEmpty()
            ) {
                $mushChance = 0;
            }
            $chancesArray[$characterConfig->getCharacterName()] = $mushChance;
        }

        $mushNumber = $gameConfig->getDaedalusConfig()->getNbMush();

        $mushPlayerName = $this->randomService->getRandomElementsFromProbaArray($chancesArray, $mushNumber);
        foreach ($mushPlayerName as $playerName) {
            $mushPlayers = $daedalus
                ->getPlayers()
                ->filter(fn (Player $player) => $player->getName() === $playerName)
            ;

            if (!$mushPlayers->isEmpty()) {
                /** @var Player $currentPlayer */
                $currentPlayer = $mushPlayers->first();
                $playerEvent = new PlayerEvent(
                    $currentPlayer,
                    [DaedalusEvent::FULL_DAEDALUS],
                    $date
                );
                $this->eventService->callEvent($playerEvent, PlayerEvent::CONVERSION_PLAYER);
            }
        }

        return $daedalus;
    }

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        $player = $this->getRandomPlayersWithLessOxygen($daedalus);

        if ($this->getOxygenCapsuleCount($player) === 0) {
            $playerEvent = new PlayerEvent(
                $player,
                [EndCauseEnum::ASPHYXIA],
                $date
            );

            $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        } else {
            $capsule = $player->getEquipments()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->first();

            $this->gameEquipmentService->delete($capsule);

            $this->roomLogService->createLog(
                LogEnum::OXY_LOW_USE_CAPSULE,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $player,
                [],
                $date
            );
        }

        return $daedalus;
    }

    private function getRandomPlayersWithLessOxygen(Daedalus $daedalus): Player
    {
        $playersAlive = $daedalus->getPlayers()->getPlayerAlive();

        $playersWithLessOxygen = new PlayerCollection();
        $lessOxygenCount = 0;
        foreach ($playersAlive as $player) {
            $playerOxygenCount = $this->getOxygenCapsuleCount($player);
            if ($playersWithLessOxygen->isEmpty()) {
                $playersWithLessOxygen->add($player);
                $lessOxygenCount = $playerOxygenCount;
            } elseif ($playerOxygenCount === $lessOxygenCount) {
                $playersWithLessOxygen->add($player);
            } elseif ($playerOxygenCount < $lessOxygenCount) {
                $playersWithLessOxygen = new PlayerCollection([$player]);
            }
        }

        return $this->randomService->getRandomPlayer($playersWithLessOxygen);
    }

    private function getOxygenCapsuleCount(Player $player): int
    {
        return $player->getEquipments()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->count();
    }

    public function killRemainingPlayers(Daedalus $daedalus, array $reasons, \DateTime $date): Daedalus
    {
        $playerAliveNb = $daedalus->getPlayers()->getPlayerAlive()->count();
        for ($i = 0; $i < $playerAliveNb; ++$i) {
            $player = $this->randomService->getAlivePlayerInDaedalus($daedalus);

            $playerEvent = new PlayerEvent(
                $player,
                $reasons,
                $date
            );
            $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        return $daedalus;
    }

    public function changeVariable(string $variableName, Daedalus $daedalus, int $change, \DateTime $date): Daedalus
    {
        $gameVariable = $daedalus->getVariableByName($variableName);

        $newVariableValuePoint = $gameVariable->getValue() + $change;
        $maxVariableValuePoint = $gameVariable->getMaxValue();
        $newVariableValuePoint = $this->getValueInInterval($newVariableValuePoint, 0, $maxVariableValuePoint);

        $daedalus->setVariableValueByName($newVariableValuePoint, $variableName);

        switch ($variableName) {
            case DaedalusVariableEnum::HULL:
                if ($newVariableValuePoint === 0) {
                    $daedalusEvent = new DaedalusEvent(
                        $daedalus,
                        [EndCauseEnum::DAEDALUS_DESTROYED],
                        $date
                    );

                    $this->eventService->callEvent($daedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
                }
                break;
        }

        return $daedalus;
    }

    public function findAllNonFinishedDaedaluses(): DaedalusCollection
    {
        return new DaedalusCollection($this->daedalusRepository->findNonFinishedDaedaluses());
    }

    private function getValueInInterval(int $value, ?int $min, ?int $max): int
    {
        if ($max !== null && $value > $max) {
            return $max;
        } elseif ($min !== null && $value < $min) {
            return $min;
        }

        return $value;
    }
}
