<?php

namespace Mush\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;

abstract class AbstractHunterEvent extends AbstractGameEvent
{
    public const MAKE_HUNTERS_SHOOT = 'make.hunters.shoot';

    protected Daedalus $daedalus;

    public function __construct(Daedalus $daedalus, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
