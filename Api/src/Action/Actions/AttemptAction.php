<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Service\ActionServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Game\Service\RandomServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AttemptAction extends AbstractAction
{
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService
    ) {
        $this->randomService = $randomService;
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
    }

    protected function checkResult(): ActionResult
    {
        $successChance = $this->getSuccessRate();

        if ($this->randomService->isSuccessful($successChance)) {
            return new Success();
        } else {
            return new Fail();
        }
    }

    public function getSuccessRate(): int
    {
        return $this->actionService->getSuccessRate($this->action, $this->player, $this->parameter);
    }
}
