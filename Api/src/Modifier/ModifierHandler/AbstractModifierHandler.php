<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Event\ModifierEvent;

abstract class AbstractModifierHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain;

    protected function addModifierEvent(
        EventChain $eventCollection,
        GameModifier $modifier,
        array $tags,
        \DateTime $time
    ): EventChain {
        $priority = $modifier->getModifierConfig()->getPriorityAsInteger();

        // no event in the chain should have a priority of 0 (restricted to the initialEvent)
        if ($priority === 0) {
            $priority = -1;
        }

        $modifierEvent = new ModifierEvent($modifier, $tags, $time);
        $modifierEvent->setEventName(ModifierEvent::APPLY_MODIFIER);
        $modifierEvent->setPriority($priority);

        $eventCollection = $eventCollection->addEvent($modifierEvent);

        return $eventCollection;
    }
}
