<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

class PlayerCycleEvent extends AbstractGameEvent
{
    public const PLAYER_NEW_CYCLE = 'player.new.cycle';
    public const PLAYER_NEW_DAY = 'player.new.day';

    protected Player $player;

    public function __construct(
        Player $player,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($reason, $time);

        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
