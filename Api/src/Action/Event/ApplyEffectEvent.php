<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Event\LoggableEventInterface;

class ApplyEffectEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const CONSUME = 'action.consume';
    public const HEAL = 'action.heal';
    public const REPORT_FIRE = 'report.fire';
    public const REPORT_EQUIPMENT = 'report.equipment';
    public const PLAYER_GET_SICK = 'player.get.sick';
    public const PLAYER_CURE_INJURY = 'player.cure.injury';

    private Player $player;
    private string $visibility;
    private ?LogParameterInterface $parameter;

    public function __construct(
        Player $player,
        ?LogParameterInterface $parameter,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        $this->player = $player;
        $this->visibility = $visibility;
        $this->parameter = $parameter;

        parent::__construct($tags, $time);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getParameter(): ?LogParameterInterface
    {
        return $this->parameter;
    }

    public function getLogParameters(): array
    {
        $logParameters = [
            'character' => $this->player->getLogName(),
            'place' => $this->player->getPlace()->getName(),
        ];

        if (($actionParameter = $this->getParameter()) !== null) {
            'target_' . $logParameters[$actionParameter->getLogKey()] = $actionParameter->getLogName();
        }

        return $logParameters;
    }
}
