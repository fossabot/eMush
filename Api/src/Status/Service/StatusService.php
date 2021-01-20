<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Repository\StatusRepository;
use Mush\Status\Repository\StatusServiceRepository;

class StatusService implements StatusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;

    public function __construct(EntityManagerInterface $entityManager, StatusRepository $statusRepository)
    {
        $this->entityManager = $entityManager;
        $this->statusRepository = $statusRepository;
    }

    public function getStatusTargetingGameEquipment(GameEquipment $gameEquipment, string $statusName): ?Status
    {
        return $this->statusRepository->findStatusTargetingGameEquipment($gameEquipment, $statusName);
    }

    public function createCorePlayerStatus(string $statusName, Player $player): Status
    {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $player->addStatus($status);

        return $status;
    }

    public function createCoreEquipmentStatus(string $statusName, GameEquipment $gameEquipment, string $visibilty = VisibilityEnum::PUBLIC): Status
    {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility($visibilty)
        ;

        $gameEquipment->addStatus($status);

        return $status;
    }

    public function createCoreRoomStatus(string $statusName, Room $room, string $visibilty = VisibilityEnum::PUBLIC): Status
    {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility($visibilty)
            ->setTarget($room)
        ;

        return $status;
    }

    public function createChargeEquipmentStatus(
        string $statusName,
        GameEquipment $gameEquipment,
        string $strategy,
        string $visibilty = VisibilityEnum::PUBLIC,
        string $chargeVisibilty = VisibilityEnum::PUBLIC,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus();
        $status
            ->setName($statusName)
            ->setStrategy($strategy)
            ->setVisibility($visibilty)
            ->setChargeVisibility($chargeVisibilty)
            ->setCharge($charge)
            ->setAutoRemove($autoRemove)
        ;

        if ($threshold) {
            $status->setThreshold($threshold);
        }

        $gameEquipment->addStatus($status);

        return $status;
    }

    public function createChargePlayerStatus(
        string $statusName,
        Player $player,
        string $strategy,
        string $visibilty = VisibilityEnum::PUBLIC,
        string $chargeVisibilty = VisibilityEnum::PUBLIC,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus();
        $status
            ->setName($statusName)
            ->setStrategy($strategy)
            ->setVisibility($visibilty)
            ->setChargeVisibility($chargeVisibilty)
            ->setCharge($charge)
            ->setAutoRemove($autoRemove)
        ;

        if ($threshold) {
            $status->setThreshold($threshold);
        }

        $player->addStatus($status);

        return $status;
    }

    public function createChargeRoomStatus(
        string $statusName,
        Room $room,
        string $strategy,
        string $visibilty = VisibilityEnum::PUBLIC,
        string $chargeVisibilty = VisibilityEnum::PUBLIC,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus();
        $status
            ->setName($statusName)
            ->setStrategy($strategy)
            ->setVisibility($visibilty)
            ->setChargeVisibility($chargeVisibilty)
            ->setTarget($room)
            ->setCharge($charge)
            ->setAutoRemove($autoRemove)
        ;

        if ($threshold) {
            $status->setThreshold($threshold);
        }

        return $status;
    }

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt
    {
        $status = new Attempt();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setAction($action)
            ->setCharge(0)
        ;

        $player->addStatus($status);

        return $status;
    }

    public function createMushStatus(Player $player): ChargeStatus
    {
        $status = new ChargeStatus();
        $status
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setCharge(1)
            ->setThreshold(1)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
        ;

        $player->addStatus($status);

        return $status;
    }

    public function createSporeStatus(Player $player): ChargeStatus
    {
        $status = new ChargeStatus();
        $status
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setCharge(1)
            ->setStrategy(ChargeStrategyTypeEnum::NONE)
        ;

        $player->addStatus($status);

        return $status;
    }

    public function persist(Status $status): Status
    {
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        return $status;
    }

    public function delete(Status $status): bool
    {
        $this->entityManager->remove($status);
        $this->entityManager->flush();

        return true;
    }

    public function getMostRecent(string $statusName, Collection $equipments): gameEquipment
    {
        $pickedEquipments = $equipments
            ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getStatusByName($statusName) !== null)
        ;
        if ($pickedEquipments->isEmpty()) {
            throw new Error('no such status in item collection');
        } else {
            /** @var GameEquipment $pickedEquipment */
            $pickedEquipment = $pickedEquipments->first();
            if ($pickedEquipments->count() > 1) {
                /** @var GameEquipment $equipment */
                foreach ($pickedEquipments as $equipment) {
                    $pickedEquipmentsStatus = $pickedEquipment->getStatusByName($statusName);
                    $equipmentsStatus = $equipment->getStatusByName($statusName);
                    if ($pickedEquipmentsStatus &&
                        $equipmentsStatus &&
                        $pickedEquipmentsStatus->getCreatedAt() < $equipmentsStatus->getCreatedAt()) {
                        $pickedEquipment = $equipment;
                    }
                }
            }
        }

        return $pickedEquipment;
    }
}
