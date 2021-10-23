<?php

namespace Mush\Player\Event;

use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerEvent extends PlayerCycleEvent implements LoggableEventInterface
{
    public const NEW_PLAYER = 'new.player';
    public const DEATH_PLAYER = 'death.player';
    public const METAL_PLATE = 'metal.plate';
    public const PANIC_CRISIS = 'panic.crisis';
    public const INFECTION_PLAYER = 'infection.player';
    public const CONVERSION_PLAYER = 'conversion.player';
    public const END_PLAYER = 'end.player';

    protected string $visibility = VisibilityEnum::PRIVATE;
    protected ?CharacterConfig $characterConfig = null;

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }

    public function getLogParameters(): array
    {
        return [
            $this->player->getLogKey() => $this->player->getLogName(),
        ];
    }

    public function setCharacterConfig(CharacterConfig $characterConfig): self
    {
        $this->characterConfig = $characterConfig;

        return $this;
    }

    public function getCharacterConfig(): ?CharacterConfig
    {
        return $this->characterConfig;
    }
}
