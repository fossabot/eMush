<?php

namespace Mush\Hunter\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Event\AbstractHunterEvent;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusService;
use Psr\Log\LoggerInterface;

class HunterService implements HunterServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private LoggerInterface $logger;
    private RandomServiceInterface $randomService;
    private StatusService $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        LoggerInterface $logger,
        RandomServiceInterface $randomService,
        StatusService $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->logger = $logger;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
    }

    public function findById(int $id): ?Hunter
    {
        return $this->entityManager->getRepository(Hunter::class)->find($id);
    }

    public function killHunter(Hunter $hunter): void
    {
        $daedalus = $hunter->getDaedalus();

        $this->dropScrap($hunter);

        $daedalus->getDaedalusInfo()->getClosedDaedalus()->incrementNumberOfHuntersKilled();

        $daedalus->getAttackingHunters()->removeElement($hunter);
        $this->entityManager->remove($hunter);
        $this->persist([$daedalus]);
    }

    public function makeHuntersShoot(HunterCollection $attackingHunters): void
    {
        /** @var Hunter $hunter */
        foreach ($attackingHunters as $hunter) {
            if (!$hunter->canShoot()) {
                continue;
            }

            $successRate = $hunter->getHunterConfig()->getHitChance();
            if (!$this->randomService->isSuccessful($successRate)) {
                return;
            }

            $this->makeHunterShoot($hunter);

            // hunter gets a truce cycle after shooting
            $this->createHunterTruceCycleStatus($hunter);

            // destroy asteroid if it has shot
            if ($hunter->getName() === HunterEnum::ASTEROID) {
                $this->killHunter($hunter);
            }
        }
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    public function unpoolHunters(Daedalus $daedalus, \DateTime $time): void
    {
        $hunterPoints = $daedalus->getHunterPoints();
        $hunterTypes = HunterEnum::getAll();
        $wave = new HunterCollection();

        while ($hunterPoints > 0) {
            $hunterProbaCollection = $this->getHunterProbaCollection($daedalus, $hunterTypes);

            // do not create a hunter if not enough points
            if ($hunterPoints < $hunterProbaCollection->min()) {
                break;
            }
            $hunterNameToCreate = $this->randomService->getSingleRandomElementFromProbaCollection(
                $hunterProbaCollection
            );
            if (!is_string($hunterNameToCreate)) {
                break;
            }

            $hunter = $this->createHunterFromName($daedalus, $hunterNameToCreate);

            // do not create a hunter if max per wave is reached
            $maxPerWave = $hunter->getHunterConfig()->getMaxPerWave();
            if ($maxPerWave && $wave->getAllHuntersByType($hunter->getName())->count() > $maxPerWave) {
                $hunterTypes->removeElement($hunterNameToCreate);
                continue;
            }

            $wave->add($hunter);

            $hunterPoints -= $hunter->getHunterConfig()->getDrawCost();
            $daedalus->setHunterPoints($hunterPoints);
        }

        $wave->map(fn ($hunter) => $this->createHunterStatuses($hunter, $time));
        $this->persist($wave->toArray());
        $this->persist([$daedalus]);
    }

    private function createHunterFromName(Daedalus $daedalus, string $hunterName): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);
        if (!$hunterConfig) {
            throw new \Exception("Hunter config not found for hunter name $hunterName");
        }

        $hunter = new Hunter($hunterConfig, $daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->addHunter($hunter);

        $this->persist([$hunter, $daedalus]);

        return $hunter;
    }

    private function createHunterStatuses(Hunter $hunter, \DateTime $time): void
    {
        $hunterConfig = $hunter->getHunterConfig();
        $statuses = $hunterConfig->getInitialStatuses();

        /** @var StatusConfig $statusConfig */
        foreach ($statuses as $statusConfig) {
            $this->statusService->createStatusFromConfig(
                $statusConfig,
                $hunter,
                [HunterPoolEvent::UNPOOL_HUNTERS],
                $time
            );
        }
    }

    private function createHunterTruceCycleStatus(Hunter $hunter): void
    {
        $truceCycleStatus = $hunter->getHunterConfig()->getInitialStatuses()->filter(
            fn (StatusConfig $statusConfig) => $statusConfig->getStatusName() === HunterStatusEnum::HUNTER_CHARGE
        )->first();

        if (!$truceCycleStatus) {
            throw new \Exception('Hunter config should have a HUNTER_CHARGE status config');
        }
        $this->statusService->createStatusFromConfig(
            $truceCycleStatus,
            $hunter,
            [AbstractHunterEvent::HUNTER_SHOT],
            new \DateTime()
        );
    }

    private function dropScrap(Hunter $hunter): void
    {
        $scrapDropTable = $hunter->getHunterConfig()->getScrapDropTable();
        $numberOfDroppedScrap = $hunter->getHunterConfig()->getNumberOfDroppedScrap();

        $numberOfScrapToDrop = (int) $this->randomService->getSingleRandomElementFromProbaCollection($numberOfDroppedScrap);
        $scrapToDrop = $this->randomService->getRandomElementsFromProbaCollection($scrapDropTable, $numberOfScrapToDrop);

        foreach ($scrapToDrop as $scrap) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $scrap,
                equipmentHolder: $hunter->getSpace(),
                reasons: [HunterEvent::HUNTER_DEATH],
                time: new \DateTime(),
                visibility: VisibilityEnum::HIDDEN
            );
        }
    }

    private function getHunterProbaCollection(Daedalus $daedalus, ArrayCollection $hunterTypes): ProbaCollection
    {
        $difficultyMode = $daedalus->getDifficultyMode();
        $probaCollection = new ProbaCollection();

        foreach ($hunterTypes as $hunterType) {
            $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterType);
            if (!$hunterConfig) {
                $this->logger->error("Hunter config not found for hunter name $hunterType", [
                    'daedalus' => $daedalus->getId(),
                ]);
                continue;
            }

            if ($hunterConfig->getSpawnDifficulty() > $difficultyMode) {
                continue;
            }

            $probaCollection->setElementProbability($hunterType, $hunterConfig->getDrawWeight());
        }

        return $probaCollection;
    }

    private function getHunterDamage(Hunter $hunter): ?int
    {
        if ($hunter->getName() === HunterEnum::ASTEROID) {
            return $hunter->getHealth();
        }

        $hunterDamageRange = $hunter->getHunterConfig()->getDamageRange();

        return (int) $this->randomService->getSingleRandomElementFromProbaCollection($hunterDamageRange);
    }

    private function makeHunterShoot(Hunter $hunter): void
    {
        $damage = $this->getHunterDamage($hunter);
        if (!$damage) {
            return;
        }

        // TODO: handle other targets
        switch ($hunter->getTarget()) {
            case HunterTargetEnum::DAEDALUS:
                $this->shootAtDaedalus($hunter, $damage);
                break;
            case HunterTargetEnum::PATROL_SHIP:
                break;
            default:
                throw new \Exception("Unknown hunter target {$hunter->getTarget()}");
        }
    }

    private function selectHunterTarget(Hunter $hunter): void
    {   
        $targetProbabilities = $hunter->getHunterConfig()->getTargetProbabilities();
        
        // @TODO if Meridon Scramber project is not completed, remove Hunter target
        $daedalusProjects = new ArrayCollection([]);
        if (!$daedalusProjects->contains('Meridon Scrambler')) {
            $targetProbabilities->removeElement(HunterTargetEnum::HUNTER);
        }

        // @TODO if there is no merchant ship in battle, remove merchant ship target
        $merchantShips = new ArrayCollection([]);
        if ($merchantShips->isEmpty()) {
            $targetProbabilities->removeElement(HunterTargetEnum::MERCHANT_SHIP);
        }

        //if there is no patrol ship in battle, remove patrol ship target
        $patrolShips = RoomEnum::getPatrolShips()
            ->map(fn (string $patrolShip) => $this->gameEquipmentService->findByNameAndDaedalus($patrolShip, $hunter->getDaedalus())->first())
            ->filter(fn ($patrolShip) => $patrolShip instanceof GameEquipment)
        ;
        $patrolShipsInBattle = $patrolShips->filter(fn (GameEquipment $patrolShip) => $patrolShip->getPlace()->getType() === PlaceTypeEnum::PATROL_SHIP);
        if ($patrolShipsInBattle->isEmpty()) {
            $targetProbabilities->removeElement(HunterTargetEnum::PATROL_SHIP);
        }

        //if there is no player in battle, remove player target
        $playersInBattle = $patrolShipsInBattle->filter(fn (GameEquipment $patrolShip) => $patrolShip->getPlace()->getNumberOfPlayersAlive() > 0);
        if ($playersInBattle->isEmpty()) {
            $targetProbabilities->removeElement(HunterTargetEnum::PLAYER);
        }

        $selectedTarget = $this->randomService->getSingleRandomElementFromProbaCollection($targetProbabilities);
        if (!$selectedTarget) {
            return;
        }

        $hunter->setTarget($selectedTarget);
    }

    private function shootAtDaedalus(Hunter $hunter, int $damage): void
    {
        $daedalusVariableEvent = new DaedalusVariableEvent(
            daedalus: $hunter->getDaedalus(),
            variableName: DaedalusVariableEnum::HULL,
            quantity: -$damage,
            tags: [AbstractHunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        $this->eventService->callEvent($daedalusVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
