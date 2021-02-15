<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;

    private ActionModifierServiceInterface $actionModifierService;
    private StatusServiceInterface $statusService;

    public function __construct(
        ActionModifierServiceInterface $actionModifierService,
        StatusServiceInterface $statusService
    ) {
        $this->actionModifierService = $actionModifierService;
        $this->statusService = $statusService;
    }

    public function canPlayerDoAction(Player $player, Action $action): bool
    {
        return $this->getTotalActionPointCost($player, $action) <= $player->getActionPoint() &&
            ($this->getTotalMovementPointCost($player, $action) <= $player->getMovementPoint() || $player->getActionPoint() > 0) &&
            $this->getTotalMoralPointCost($player, $action) <= $player->getMoralPoint()
        ;
    }

    public function applyCostToPlayer(Player $player, Action $action): Player
    {
        return $player
            ->addActionPoint((-1) * $this->getTotalActionPointCost($player, $action))
            ->addMovementPoint((-1) * $this->getTotalMovementPointCost($player, $action))
            ->addMoralPoint((-1) * $this->getTotalMoralPointCost($player, $action))
            ;
    }

    public function getTotalActionPointCost(Player $player, Action $action): ?int
    {
        if ($action->getActionCost()->getActionPointCost() !== null) {
            $modifiersDelta = $this->actionModifierService->getAdditiveModifier(
                $player,
                array_merge([$action->getName()], $action->getTypes()),
                ModifierTargetEnum::ACTION_POINT
            );

            return (int) max($action->getActionCost()->getActionPointCost() + $modifiersDelta, 0);
        }

        return null;
    }

    public function getTotalMovementPointCost(Player $player, Action $action): ?int
    {
        if ($action->getActionCost()->getMovementPointCost() !== null) {
            $modifiersDelta = $this->actionModifierService->getAdditiveModifier(
                $player,
                array_merge([$action->getName()], $action->getTypes()),
                ModifierTargetEnum::MOVEMENT_POINT
            );

            return (int) max($action->getActionCost()->getMovementPointCost() + $modifiersDelta, 0);
        }

        return null;
    }

    public function getTotalMoralPointCost(Player $player, Action $action): ?int
    {
        return $action->getActionCost()->getMoralPointCost();
    }

    public function getSuccessRate(
        Action $action,
        Player $player,
        int $baseRate,
    ): int {
        //Get number of attempt
        $numberOfAttempt = $this->getAttempt($player, $action->getName())->getCharge();

        //Get modifiers
        $modificator = $this->actionModifierService->getMultiplicativeModifier(
            $player,
            array_merge([$action->getName()], $action->getTypes()),
            ModifierTargetEnum::PERCENTAGE
        );

        dump($modificator);

        return $this->computeSuccessRate($baseRate, $numberOfAttempt, $modificator);
    }

    public function getAttempt(Player $player, string $actionName): Attempt
    {
        /** @var Attempt $attempt */
        $attempt = $player->getStatusByName(StatusEnum::ATTEMPT);

        if ($attempt && $attempt->getAction() !== $actionName) {
            // Re-initialize attempts with new action
            $attempt
                ->setAction($actionName)
                ->setCharge(0)
            ;
        } elseif ($attempt === null) { //Create Attempt
            $attempt = $this->statusService->createAttemptStatus(
                StatusEnum::ATTEMPT,
                $actionName,
                $player
            );
        }

        return $attempt;
    }

    public function computeSuccessRate(
        int $baseRate,
        int $numberOfAttempt,
        float $relativeModificator,
        float $fixedModificator = 0
    ): int {
        return (int) min(
            ($baseRate * (1.25) ** $numberOfAttempt) * $relativeModificator + $baseRate * $fixedModificator,
            self::MAX_PERCENT
        );
    }
}
