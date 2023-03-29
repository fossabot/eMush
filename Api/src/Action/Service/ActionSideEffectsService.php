<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionVariables;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class ActionSideEffectsService implements ActionSideEffectsServiceInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
    }

    public function handleActionSideEffect(Action $action, Player $player, ?LogParameterInterface $parameter): Player
    {
        $this->handleDirty($action, $player, $parameter);
        $this->handleInjury($action, $player, $parameter);

        return $player;
    }

    private function handleDirty(Action $action, Player $player, ?LogParameterInterface $parameter): void
    {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $actionEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_DIRTINESS,
            $action->getActionVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_DIRTINESS),
            $player,
            $parameter
        );

        $this->eventService->callEvent($actionEvent, ActionVariableEvent::ROLL_PERCENTAGE_DIRTY);
    }

    private function handleInjury(Action $action, Player $player, ?LogParameterInterface $parameter): void
    {
        $actionEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_INJURY,
            $action->getActionVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_INJURY),
            $player,
            $parameter
        );

        $this->eventService->callEvent($actionEvent, ActionVariableEvent::ROLL_PERCENTAGE_INJURY);
    }
}
