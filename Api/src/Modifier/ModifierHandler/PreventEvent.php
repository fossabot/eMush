<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;

class PreventEvent extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::PREVENT_EVENT;

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        $events->stopEvents($modifier->getModifierConfig()->getPriority());

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
