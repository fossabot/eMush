<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Entity extends EquipmentMechanic
{
    /**
     * Entity constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::ENTITY;
    }
}
