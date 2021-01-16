<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Entity\CharacterConfig;

class CharacterConfigCollection extends ArrayCollection
{
    public function getCharacter(string $name): ?CharacterConfig
    {
        $character = $this
            ->filter(fn (CharacterConfig $characterConfig) => $characterConfig->getName() === $name)
            ->first()
        ;

        return $character === false ? null : $character;
    }
}
