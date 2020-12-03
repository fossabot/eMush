<?php

namespace Mush\Equipment\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Equipment\Entity\EquipmentMechanic;

class EquipmentCycleHandlerService implements EquipmentCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractCycleHandler $cycleHandler)
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
