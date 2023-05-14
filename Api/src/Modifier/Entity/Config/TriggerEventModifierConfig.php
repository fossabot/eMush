<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;

/**
 * One of the modifier type
 * This type of modifier trigger an additional event when the target event is dispatched.
 *
 * visibility: the visibility of the triggered event
 * triggeredEventConfig: a config to create the triggered event
 * replaceEvent: does the new event replace the initial one?
 */
#[ORM\Entity]
class TriggerEventModifierConfig extends EventModifierConfig
{
    #[ORM\ManyToOne(targetEntity: AbstractEventConfig::class)]
    protected ?AbstractEventConfig $triggeredEvent;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $replaceEvent = false;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $visibility = VisibilityEnum::PUBLIC;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->addNoneTagName();
    }

    public function buildName(string $configName): self
    {
        $baseName = $this->modifierName;
        $triggeredEvent = $this->triggeredEvent;

        if ($baseName === null && $triggeredEvent !== null) {
            $baseName = $triggeredEvent->getName();
        } elseif ($baseName === null) {
            $baseName = 'prevent';
        }

        $this->name = $baseName . '_ON_' . $this->getTargetEvent() . '_' . $configName;

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $this->name = $this->name . '_if_' . $requirement->getName();
        }

        $this->addNoneTagName();

        return $this;
    }

    public function setName(string $name): self
    {
        parent::setName($name);
        $this->addNoneTagName();

        return $this;
    }

    public function setModifierName(string|null $modifierName): self
    {
        parent::setModifierName($modifierName);
        $this->addNoneTagName();

        return $this;
    }

    // this prevents infinite loop where triggeredEvent can trigger itself
    private function addNoneTagName(): void
    {
        $modifierName = $this->modifierName;

        if ($modifierName === null) {
            $modifierName = $this->name;
            $this->modifierName = $modifierName;
        }

        $this->tagConstraints[$modifierName] = ModifierRequirementEnum::NONE_TAGS;
    }

    public function setTagConstraints(array $tagConstraints): self
    {
        parent::setTagConstraints($tagConstraints);

        $this->addNoneTagName();

        return $this;
    }

    public function getTriggeredEvent(): ?AbstractEventConfig
    {
        return $this->triggeredEvent;
    }

    public function setTriggeredEvent(?AbstractEventConfig $triggeredEvent): self
    {
        $this->triggeredEvent = $triggeredEvent;

        return $this;
    }

    public function getReplaceEvent(): bool
    {
        return $this->replaceEvent;
    }

    public function setReplaceEvent(bool $replaceEvent): self
    {
        $this->replaceEvent = $replaceEvent;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }
}
