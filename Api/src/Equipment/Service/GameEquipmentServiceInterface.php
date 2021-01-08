<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;

interface GameEquipmentServiceInterface
{
    public function persist(GameEquipment $equipment): GameEquipment;

    public function delete(GameEquipment $equipment): void;

    public function findById(int $id): ?GameEquipment;

    public function createGameEquipmentFromName(string $equipmentName, Daedalus $daedalus): GameEquipment;

    public function createGameEquipment(EquipmentConfig $equipment, Daedalus $daedalus): GameEquipment;

    public function getOperationalEquipmentsByName(string $equipmentName, Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection;

    public function isOperational(GameEquipment $equipment): bool;

    public function handleBreakCycle(GameEquipment $gameEquipment): void;
}
