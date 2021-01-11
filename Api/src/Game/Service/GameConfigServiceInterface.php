<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\Collection\TriumphConfigCollection;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;

interface GameConfigServiceInterface
{
    public function getConfig(): GameConfig;

    public function getDifficultyConfig(): DifficultyConfig;

    public function getTriumphConfig(): TriumphConfigCollection;
}
