<?php

namespace Mush\Equipment\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class ItemCollection extends ArrayCollection
{
    public function getByStatusName(string $statusName): Collection
    {
        return $this->filter(fn (GameItem $gameItem) => $gameItem->getStatusByName($statusName));
    }
}
