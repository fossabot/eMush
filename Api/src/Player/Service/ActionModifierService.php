<?php

namespace Mush\Player\Service;

use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ActionModifierService implements ActionModifierServiceInterface
{
    const FULL_STOMACH_STATUS_THRESHOLD = 3;
    const STARVING_STATUS_THRESHOLD = -24;

    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfigServiceInterface $gameConfigService;

    public function __construct(StatusServiceInterface $statusService, RoomLogServiceInterface $roomLogService, GameConfigServiceInterface $gameConfigService)
    {
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->gameConfigService = $gameConfigService;
    }

    public function handlePlayerModifier(Player $player, ActionModifier $actionModifier, \DateTime $date = null): Player
    {
        $date = $date ?? new \DateTime('now');
        $player = $this->handleActionPointModifier($actionModifier, $player, $date);
        $player = $this->handleMovementPointModifier($actionModifier, $player, $date);
        $player = $this->handleHealthPointModifier($actionModifier, $player, $date);
        $player = $this->handleMoralPointModifier($actionModifier, $player, $date);
        $player = $this->handleSatietyModifier($actionModifier, $player);

        return $player;
    }

    private function handleActionPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier->getActionPointModifier() !== 0) {
            $playerNewActionPoint = $player->getActionPoint() + $actionModifier->getActionPointModifier();
            $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $gameConfig->getMaxActionPoint());
            $player->setActionPoint($playerNewActionPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getActionPointModifier() > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                abs($actionModifier->getActionPointModifier()),
                $date
            );
        }

        return $player;
    }

    private function handleMovementPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($actionModifier->getMovementPointModifier()) {
            $playerNewMovementPoint = $player->getMovementPoint() + $actionModifier->getMovementPointModifier();
            $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $gameConfig->getMaxMovementPoint());
            $player->setMovementPoint($playerNewMovementPoint);
            $this->roomLogService->createQuantityLog(
                $actionModifier->getMovementPointModifier() > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                abs($actionModifier->getMovementPointModifier()),
                $date
            );
        }

        return $player;
    }

    private function handleHealthPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($healthPoints = $actionModifier->getHealthPointModifier()) {
            $playerNewHealthPoint = $player->getHealthPoint() + $healthPoints;
            $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $gameConfig->getMaxHealthPoint());
            $player->setHealthPoint($playerNewHealthPoint);
            $this->roomLogService->createQuantityLog(
                $healthPoints > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                abs($healthPoints),
                $date
            );
        }

        return $player;
    }

    private function handleMoralPointModifier(ActionModifier $actionModifier, Player $player, \DateTime $date): Player
    {
        $gameConfig = $this->gameConfigService->getConfig();

        if ($moralPoints = $actionModifier->getMoralPointModifier()) {
            if (!$player->isMush()) {
                $playerNewMoralPoint = $player->getMoralPoint() + $moralPoints;
                $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $gameConfig->getMaxMoralPoint());
                $player->setMoralPoint($playerNewMoralPoint);

                $player = $this->handleMoralStatus($player);

                $this->roomLogService->createQuantityLog(
                    $moralPoints > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    abs($moralPoints),
                    $date
                );
            }
        }

        return $player;
    }

    private function handleMoralStatus(Player $player): Player
    {
        $demoralizedStatus = $player->getStatusByName(PlayerStatusEnum::DEMORALIZED);
        $suicidalStatus = $player->getStatusByName(PlayerStatusEnum::SUICIDAL);

        if ($player->getMoralPoint() <= 1 && !$suicidalStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::SUICIDAL, $player);
        } elseif ($suicidalStatus) {
            $player->removeStatus($suicidalStatus);
        }

        if ($player->getMoralPoint() <= 4 && $player->getMoralPoint() > 1 && $demoralizedStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::DEMORALIZED, $player);
        } elseif ($demoralizedStatus) {
            $player->removeStatus($demoralizedStatus);
        }

        return $player;
    }

    private function handleSatietyModifier(ActionModifier $actionModifier, Player $player): Player
    {
        if ($actionModifier->getSatietyModifier()) {
            if ($actionModifier->getSatietyModifier() >= 0 &&
                $player->getSatiety() < 0) {
                $player->setSatiety($actionModifier->getSatietyModifier());
            } else {
                $player->setSatiety($player->getSatiety() + $actionModifier->getSatietyModifier());
            }

            $player = $this->handleSatietyStatus($actionModifier, $player);
        }

        return $player;
    }

    private function handleSatietyStatus(ActionModifier $actionModifier, Player $player): Player
    {
        if (!$player->isMush()) {
            $player = $this->handleHumanStatus($player);
        } elseif ($actionModifier->getSatietyModifier() >= 0) {
            $this->statusService->createChargePlayerStatus(
                PlayerStatusEnum::FULL_STOMACH,
                $player,
                ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                VisibilityEnum::PUBLIC,
                2,
                0,
                true
            );
        }

        return $player;
    }

    private function handleHumanStatus(Player $player): Player
    {
        $starvingStatus = $player->getStatusByName(PlayerStatusEnum::STARVING);
        $fullStatus = $player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);

        if ($player->getSatiety() < self::STARVING_STATUS_THRESHOLD && !$starvingStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::STARVING, $player);
        } elseif ($player->getSatiety() >= self::STARVING_STATUS_THRESHOLD && $starvingStatus) {
            $player->removeStatus($starvingStatus);
        }

        if ($player->getSatiety() >= self::FULL_STOMACH_STATUS_THRESHOLD && !$fullStatus) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::FULL_STOMACH, $player);
        } elseif ($fullStatus) {
            $player->removeStatus($fullStatus);
        }

        return $player;
    }

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
