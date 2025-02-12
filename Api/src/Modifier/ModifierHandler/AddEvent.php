<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Service\EventCreationServiceInterface;

class AddEvent extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::ADD_EVENT;

    private EventCreationServiceInterface $eventCreationService;

    public function __construct(
        EventCreationServiceInterface $eventCreationService,
    ) {
        $this->eventCreationService = $eventCreationService;
    }

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        /** @var TriggerEventModifierConfig $modifierConfig */
        $modifierConfig = $modifier->getModifierConfig();
        $eventConfig = $modifierConfig->getTriggeredEvent();

        $priority = $modifierConfig->getPriorityAsInteger();
        if ($priority === 0) {
            throw new \Exception('Modifier cannot have a priority of 0 (restricted to the initialEvent)');
        }
        $tags[] = $modifier->getModifierConfig()->getModifierName() ?: $modifier->getModifierConfig()->getName();

        $newEvents = $this->eventCreationService->createEvents(
            $eventConfig,
            $modifier->getModifierHolder(),
            $priority,
            $tags,
            $time
        );

        $events = $events->addEvents($newEvents);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
