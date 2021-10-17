<?php

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\Config\EquipmentMechanic;
use Mush\Game\CycleHandler\AbstractCycleHandler;

class EquipmentCycleHandlerService implements EquipmentCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractCycleHandler $cycleHandler): void
    {
        $this->strategies[$cycleHandler->getName()] = $cycleHandler;
    }

    public function getEquipmentCycleHandler(EquipmentMechanic $mechanic): ?AbstractCycleHandler
    {
        if (!isset($this->strategies[$mechanic->getMechanic()])) {
            return null;
        }

        return $this->strategies[$mechanic->getMechanic()];
    }
}
