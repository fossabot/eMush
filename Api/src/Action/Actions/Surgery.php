<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AreMedicalSuppliesOnReach;
use Mush\Action\Validator\HasDiseases;
use Mush\Action\Validator\HasStatus;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement surgery action
 * A medic can perform a surgery in medlab or if it holds the medikit
 * For 2 Action Points, medic can heal one injury of a player that is lying down
 * There is a chance to fail and give a septis
 * There is a chance for a critical success that grant the player extra triumph.
 *
 * More info : http://mushpedia.com/wiki/Medic
 */
class Surgery extends AbstractAction
{
    protected string $name = ActionEnum::SURGERY;

    private const FAIL_CHANCES = 10;
    private const CRITICAL_SUCCESS_CHANCES = 15;

    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'target' => HasStatus::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SURGERY_NOT_LYING_DOWN,
        ]));
        $metadata->addConstraint(new AreMedicalSuppliesOnReach([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasDiseases([
            'groups' => ['visibility'],
            'target' => HasDiseases::PARAMETER,
            'isEmpty' => false,
            'type' => TypeEnum::INJURY,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $result = $this->randomService->outputCriticalChances(
            $this->getModifiedPercentage(self::FAIL_CHANCES),
            0,
            $this->getModifiedPercentage(self::CRITICAL_SUCCESS_CHANCES, ActionVariableEnum::PERCENTAGE_CRITICAL)
        );

        if ($result === ActionOutputEnum::FAIL) {
            return new Fail();
        } elseif ($result === ActionOutputEnum::CRITICAL_SUCCESS) {
            return new CriticalSuccess();
        } elseif ($result === ActionOutputEnum::SUCCESS) {
            return new Success();
        }

        return new Error('this output should not exist');
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $targetPlayer */
        $targetPlayer = $this->target;
        $date = new \DateTime();

        if ($result instanceof Fail) {
            $this->failedSurgery($targetPlayer, $date);
        } elseif ($result instanceof CriticalSuccess) {
            $this->successSurgery($targetPlayer, ActionOutputEnum::CRITICAL_SUCCESS, $date);
        } elseif ($result instanceof Success) {
            $this->successSurgery($targetPlayer, ActionOutputEnum::SUCCESS, $date);
        }
    }

    private function successSurgery(Player $targetPlayer, string $result, \DateTime $time): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $targetPlayer,
            VisibilityEnum::PUBLIC,
            [$this->getActionName() . '_' . $result],
            $time
        );

        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_CURE_INJURY);
    }

    private function failedSurgery(Player $targetPlayer, \DateTime $time): ActionResult
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $targetPlayer,
            VisibilityEnum::PUBLIC,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);

        return new Fail();
    }

    private function getModifiedPercentage(int $percentage, string $mode = ActionVariableEnum::PERCENTAGE_SUCCESS): int
    {
        $criticalRollEvent = new ActionVariableEvent(
            $this->action,
            $mode,
            $percentage,
            $this->player,
            $this->target
        );

        /** @var ActionVariableEvent $criticalRollEvent */
        $criticalRollEvent = $this->eventService->computeEventModifications($criticalRollEvent, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);

        return $criticalRollEvent->getRoundedQuantity();
    }
}
