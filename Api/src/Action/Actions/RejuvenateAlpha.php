<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Event\Service\EventService;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RejuvenateAlpha extends AbstractAction
{
    protected string $name = ActionEnum::REJUVENATE_ALPHA;

    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;

    public function __construct(
        EventService $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    protected function applyEffects(): ActionResult
    {
        $maxActionPoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::ACTION_POINT);
        $maxMovementPoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::MOVEMENT_POINT);
        $maxMoralePoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::MORAL_POINT);
        $maxHealthPoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::HEALTH_POINT);

        $this->player
            ->setActionPoint($maxActionPoint)
            ->setMovementPoint($maxMovementPoint)
            ->setMoralPoint($maxMoralePoint)
            ->setHealthPoint($maxHealthPoint)
        ;

        $this->playerService->persist($this->player);

        return new Success();
    }
}
