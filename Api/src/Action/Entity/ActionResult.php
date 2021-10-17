<?php

namespace Mush\Action\ActionResult;

use Mush\Equipment\Entity\Config\GameEquipment;

abstract class ActionResult
{
    private ?GameEquipment $equipment = null;
    private ?int $quantity = null;

    public function setEquipment(GameEquipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?GameEquipment
    {
        return $this->equipment;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
}
