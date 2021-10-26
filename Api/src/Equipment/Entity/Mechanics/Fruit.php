<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Fruit extends Ration
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $plantName;

    /**
     * Fruit constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::FRUIT;
    }

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    /**
     * @return static
     */
    public function setPlantName(string $plantName): self
    {
        $this->plantName = $plantName;

        return $this;
    }
}
