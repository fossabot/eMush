<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Fruit extends Ration
{
    protected string $mechanic = EquipmentMechanicEnum::FRUIT;

    protected array $actions = [ActionEnum::TRANSPLANT];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $plantName = null;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $fruitEffectsNumber = [0];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesName = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesEffectChance = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesEffectDelayMin = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesEffectDelayLength = [];

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    public function setPlantName(string $plantName): Fruit
    {
        $this->plantName = $plantName;

        return $this;
    }

    public function getFruitEffectsNumber(): array
    {
        return $this->fruitEffectsNumber;
    }

    public function setFruitEffectsNumber(array $fruitEffectsNumber): Fruit
    {
        $this->fruitEffectsNumber = $fruitEffectsNumber;

        return $this;
    }

    public function getDiseasesName(): array
    {
        return $this->diseasesName;
    }

    public function setDiseasesName(array $diseasesName): Fruit
    {
        $this->diseasesName = $diseasesName;

        return $this;
    }

    public function getDiseasesEffectChance(): array
    {
        return $this->diseasesEffectChance;
    }

    public function setDiseasesEffectChance(array $diseasesEffectChance): Fruit
    {
        $this->diseasesEffectChance = $diseasesEffectChance;

        return $this;
    }

    public function getDiseasesEffectDelayMin(): array
    {
        return $this->diseasesEffectDelayMin;
    }

    public function setDiseasesEffectDelayMin(array $diseasesEffectDelayMin): Fruit
    {
        $this->diseasesEffectDelayMin = $diseasesEffectDelayMin;

        return $this;
    }

    public function getDiseasesEffectDelayLength(): array
    {
        return $this->diseasesEffectDelayLength;
    }

    public function setDiseasesEffectDelayLength(array $diseasesEffectDelayLength): Fruit
    {
        $this->diseasesEffectDelayLength = $diseasesEffectDelayLength;

        return $this;
    }
}
