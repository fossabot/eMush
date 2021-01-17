<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Entity\TriumphConfig;

class TriumphConfigCollection extends ArrayCollection
{
    public function getTriumph(string $name): ?TriumphConfig
    {
        $triumph = $this
            ->filter(fn (TriumphConfig $triumphConfig) => $triumphConfig->getName() === $name)
            ->first()
        ;

        return $triumph === false ? null : $triumph;
    }
}
