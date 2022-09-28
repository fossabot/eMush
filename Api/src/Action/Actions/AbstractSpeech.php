<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;

/**
 * Class implementing a generic speech action.
 * For more info, see `MotivationalSpeech` and
 * `BoringSpeech` classes.
 */
abstract class AbstractSpeech extends AbstractAction
{
    protected string $name;
    protected string $playerVariable;
    protected int $gain;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    protected function addVariablePoints(Player $player, string $playerVariable, int $points): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            $playerVariable,
            $points,
            $this->getActionName(),
            new \DateTime()
        );

        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $speaker = $this->player;
        $listeners = $this->player->getPlace()->getPlayers()
                    ->filter(function (Player $player) use ($speaker) {
                        return $player !== $speaker;
                    });

        foreach ($listeners as $player) {
            $this->addVariablePoints($player, $this->playerVariable, $this->gain);
        }
    }
}
