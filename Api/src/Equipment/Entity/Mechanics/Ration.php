<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Ration extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::RATION;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $moralPoints = [0 => 1];
    //  possibilities are stored as key, array value represent the probability to get the key value

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $actionPoints = [0 => 1];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $movementPoints = [0 => 1];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $healthPoints = [0 => 1];

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $satiety = 1;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $cures = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesChances = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesDelayMin = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseasesDelayLength = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $extraEffects = [];

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $isPerishable = true;

    //Rations currently only have consume Action
    public function setActions(array $actions): Ration
    {
        return $this;
    }

    public function getActions(): array
    {
        return [ActionEnum::CONSUME];
    }

    public function getActionPoints(): array
    {
        return $this->actionPoints;
    }

    public function setActionPoints(array $actionPoints): Ration
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }

    public function getMovementPoints(): array
    {
        return $this->movementPoints;
    }

    public function setMovementPoints(array $movementPoints): Ration
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }

    public function getHealthPoints(): array
    {
        return $this->healthPoints;
    }

    public function setHealthPoints(array $healthPoints): Ration
    {
        $this->healthlPoints = $healthPoints;

        return $this;
    }

    public function getMoralPoints(): array
    {
        return $this->moralPoints;
    }

    public function setMoralPoints(array $moralPoints): Ration
    {
        $this->moralPoints = $moralPoints;

        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    public function setSatiety(int $satiety): Ration
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function getCures(): array
    {
        return $this->cures;
    }

    public function setCures(array $cures): Ration
    {
        $this->cures = $cures;

        return $this;
    }

    public function getDiseasesChances(): array
    {
        return $this->diseasesChances;
    }

    public function setDiseasesChances(array $diseasesChances): Ration
    {
        $this->diseasesChances = $diseasesChances;

        return $this;
    }

    public function getDiseasesDelayMin(): array
    {
        return $this->diseasesDelayMin;
    }

    public function setDiseasesDelayMin(array $diseasesDelayMin): Ration
    {
        $this->diseasesDelayMin = $diseasesDelayMin;

        return $this;
    }

    public function getDiseasesDelayLength(): array
    {
        return $this->diseasesDelayLength;
    }

    public function setDiseasesDelayLength(array $diseasesDelayLength): Ration
    {
        $this->diseasesDelayLength = $diseasesDelayLength;

        return $this;
    }

    public function getExtraEffects(): array
    {
        return $this->extraEffects;
    }

    public function setExtraEffects(array $extraEffects): Ration
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }

    public function isPerishable(): bool
    {
        return $this->isPerishable;
    }

    public function setIsPerishable(bool $isPerishable): Ration
    {
        $this->isPerishable = $isPerishable;

        return $this;
    }
}
