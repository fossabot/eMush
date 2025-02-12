<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;

/**
 * Class storing the various information needed to create and apply an eventModifier.
 * EventModifiers allows the creation of a game modifier that is activated whenever the target event is dispatched.
 *
 * targetEvent: the name of the event that trigger this modifier (apply modifier)
 * applyOnTarget: specify if the modifier only is applied when the holder is the target of an action (apply modifier)
 * tagConstraints: limit the application of the modifier according to the tags of the event (3 conditions possible: ALL_TAGS, ANY_TAGS and NONE_TAGS)
 * ex: ['tag1' => 'all_tags', 'tag2' => 'all_tags'] means only apply if event have tag1 AND tag2
 * ex: ['tag1' => 'any_tags', 'tag2' => 'any_tags'] means only apply if event have tag1 OR tag2
 * ex: ['tag1' => 'none_tags', 'tag2' => 'none_tags'] means only apply if event do NOT have tag1 NOR tag2
 */
#[ORM\Entity]
class EventModifierConfig extends AbstractModifierConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    protected string $targetEvent;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $modifierStrategy;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $priority = ModifierPriorityEnum::BEFORE_INITIAL_EVENT;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $applyOnTarget = false;

    #[ORM\Column(type: 'array', nullable: false)]
    protected array $tagConstraints = [];

    public function getTargetEvent(): string
    {
        return $this->targetEvent;
    }

    public function setTargetEvent(string $targetEvent): self
    {
        $this->targetEvent = $targetEvent;

        return $this;
    }

    public function getModifierStrategy(): string
    {
        return $this->modifierStrategy;
    }

    public function setModifierStrategy(string $modifierStrategy): self
    {
        $this->modifierStrategy = $modifierStrategy;

        return $this;
    }

    public function getPriorityAsInteger(): int
    {
        if (key_exists($this->priority, ModifierPriorityEnum::PRIORITY_MAP)) {
            return ModifierPriorityEnum::PRIORITY_MAP[$this->priority];
        }

        return intval($this->priority);
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getApplyOnTarget(): bool
    {
        return $this->applyOnTarget;
    }

    public function setApplyOnTarget(bool $onTargetOnly): self
    {
        $this->applyOnTarget = $onTargetOnly;

        return $this;
    }

    public function setTagConstraints(array $tagConstraints): self
    {
        $this->tagConstraints = $tagConstraints;

        return $this;
    }

    public function getTagConstraints(): array
    {
        return $this->tagConstraints;
    }

    public function doModifierApplies(AbstractGameEvent $event): bool
    {
        if ($event->getEventName() !== $this->getTargetEvent()) {
            return false;
        }

        $anyConstraint = null;

        foreach ($this->tagConstraints as $tag => $constraint) {
            switch ($constraint) {
                case ModifierRequirementEnum::ANY_TAGS:
                    if ($anyConstraint === null) {
                        $anyConstraint = false;
                    }

                    if (in_array($tag, $event->getTags())) {
                        $anyConstraint = true;
                    }
                    break;
                case ModifierRequirementEnum::ALL_TAGS:
                    if (!in_array($tag, $event->getTags())) {
                        return false;
                    }
                    break;
                case ModifierRequirementEnum::NONE_TAGS:
                    if (in_array($tag, $event->getTags())) {
                        return false;
                    }
                    break;
                default:
                    throw new \LogicException('unexpected constraint type');
            }
        }

        if ($anyConstraint === null) {
            return true;
        }

        return $anyConstraint;
    }

    public function getTranslationKey(): ?string
    {
        $name = $this->modifierName ?: $this->name;

        return $name . '_on_' . $this->targetEvent;
    }

    public function getTranslationParameters(): array
    {
        $parameters = parent::getTranslationParameters();

        $tagConstraints = $this->tagConstraints;
        foreach (array_keys($tagConstraints) as $tagKey) {
            if ($tagConstraints[$tagKey] !== ModifierRequirementEnum::NONE_TAGS
                && ActionTypeEnum::getAll()->contains($tagKey)
                || ActionEnum::getAll()->contains($tagKey)
            ) {
                $parameters['action_name'] = $tagKey;
            }
        }

        return $parameters;
    }
}
