<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasAction;
use Mush\Action\Validator\ModifierPreventAction;
use Mush\Action\Validator\PlayerAlive;
use Mush\Action\Validator\PlayerCanAffordPoints;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractAction
{
    protected Action $action;
    protected Player $player;
    protected ?LogParameterInterface $target = null;
    protected ?array $parameters = [];

    protected string $name;

    protected EventServiceInterface $eventService;
    protected ActionServiceInterface $actionService;
    protected ValidatorInterface $validator;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator
    ) {
        $this->eventService = $eventService;
        $this->actionService = $actionService;
        $this->validator = $validator;
    }

    abstract protected function support(?LogParameterInterface $target, array $parameters): bool;

    public function loadParameters(Action $action, Player $player, LogParameterInterface $target = null, array $parameters = []): void
    {
        if (!$this->support($target, $parameters)) {
            throw new \InvalidArgumentException('Invalid action parameters : one of the passed parameters from ' . json_encode($parameters) . ' is not supported.');
        }

        $this->action = $action;
        $this->player = $player;
        $this->target = $target;
        $this->parameters = $parameters;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new PlayerAlive(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasAction(['groups' => ['visibility']]));
        $metadata->addConstraint(new PlayerCanAffordPoints(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INSUFFICIENT_ACTION_POINT]));
        $metadata->addConstraint(new ModifierPreventAction(['groups' => ['execute']]));
    }

    public function isVisible(): bool
    {
        $validator = $this->validator;

        return $validator->validate($this, null, 'visibility')->count() === 0;
    }

    public function cannotExecuteReason(): ?string
    {
        $validator = $this->validator;
        $violations = $validator->validate($this, null, 'execute');

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            return (string) $violation->getMessage();
        }

        return null;
    }

    abstract protected function checkResult(): ActionResult;

    abstract protected function applyEffect(ActionResult $result): void;

    public function execute(): ActionResult
    {
        if (!$this->isVisible()
            || $this->cannotExecuteReason() !== null
        ) {
            return new Error('Cannot execute action');
        }

        $preActionEvent = new ActionEvent($this->action, $this->player, $this->target);
        $this->eventService->callEvent($preActionEvent, ActionEvent::PRE_ACTION);

        $this->actionService->applyCostToPlayer($this->player, $this->action, $this->target);

        $result = $this->checkResult();

        $result->setVisibility($this->action->getVisibility($result->getName()));

        $resultActionEvent = new ActionEvent($this->action, $this->player, $this->target);
        $resultActionEvent->setActionResult($result);
        $this->eventService->callEvent($resultActionEvent, ActionEvent::RESULT_ACTION);

        $this->applyEffect($result);

        $postActionEvent = new ActionEvent($this->action, $this->player, $this->target);
        $postActionEvent->setActionResult($result);
        $this->eventService->callEvent($postActionEvent, ActionEvent::POST_ACTION);

        return $result;
    }

    public function getActionName(): string
    {
        return $this->name;
    }

    public function getActionPointCost(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->action,
            $this->target,
            PlayerVariableEnum::ACTION_POINT
        );
    }

    public function getMovementPointCost(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->action,
            $this->target,
            PlayerVariableEnum::MOVEMENT_POINT
        );
    }

    public function getMoralPointCost(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->action,
            $this->target,
            PlayerVariableEnum::MORAL_POINT
        );
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function getTarget(): ?LogParameterInterface
    {
        return $this->target;
    }

    public function getAction(): Action
    {
        return $this->action;
    }
}
