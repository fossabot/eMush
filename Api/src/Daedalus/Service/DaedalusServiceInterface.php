<?php

namespace Mush\Daedalus\Service;

use Mush\DAaedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;

interface DaedalusServiceInterface
{
    public function persist(Daedalus $daedalus): Daedalus;

    public function findById(int $id): ?Daedalus;

    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection;

    public function findAvailableDaedalus(): ?Daedalus;

    public function createDaedalus(GameConfig $gameConfig): Daedalus;
}
