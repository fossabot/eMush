<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Place\Entity\PlaceConfig;

#[ORM\Entity]
#[ORM\Table(name: 'config_daedalus')]
class DaedalusConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initOxygen;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initFuel;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initHull;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initShield;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxOxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxHull = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxShield = 0;

    #[ORM\OneToOne(targetEntity: RandomItemPlaces::class, cascade: ['ALL'])]
    private ?RandomItemPlaces $randomItemPlace = null;

    #[ORM\ManyToMany(targetEntity: PlaceConfig::class)]
    private Collection $placeConfigs;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dailySporeNb = 4;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $nbMush = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cyclePerGameDay = 8;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleLength = 0; // in minutes

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getInitOxygen(): int
    {
        return $this->initOxygen;
    }

    public function setInitOxygen(int $initOxygen): static
    {
        $this->initOxygen = $initOxygen;

        return $this;
    }

    public function getInitFuel(): int
    {
        return $this->initFuel;
    }

    public function setInitFuel(int $initFuel): static
    {
        $this->initFuel = $initFuel;

        return $this;
    }

    public function getInitHull(): int
    {
        return $this->initHull;
    }

    public function setInitHull(int $initHull): static
    {
        $this->initHull = $initHull;

        return $this;
    }

    public function getInitShield(): int
    {
        return $this->initShield;
    }

    public function setInitShield(int $initShield): static
    {
        $this->initShield = $initShield;

        return $this;
    }

    public function getRandomItemPlace(): ?RandomItemPlaces
    {
        return $this->randomItemPlace;
    }

    public function setRandomItemPlace(RandomItemPlaces $randomItemPlace): static
    {
        $this->randomItemPlace = $randomItemPlace;

        return $this;
    }

    public function getPlaceConfigs(): Collection
    {
        return $this->placeConfigs;
    }

    public function setPlaceConfigs(Collection $placeConfigs): static
    {
        $this->placeConfigs = $placeConfigs;

        return $this;
    }

    public function getDailySporeNb(): int
    {
        return $this->dailySporeNb;
    }

    public function setDailySporeNb(int $dailySporeNb): static
    {
        $this->dailySporeNb = $dailySporeNb;

        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    public function setMaxOxygen(int $maxOxygen): static
    {
        $this->maxOxygen = $maxOxygen;

        return $this;
    }

    public function getMaxFuel(): int
    {
        return $this->maxFuel;
    }

    public function setMaxFuel(int $maxFuel): static
    {
        $this->maxFuel = $maxFuel;

        return $this;
    }

    public function getMaxHull(): int
    {
        return $this->maxHull;
    }

    public function setMaxHull(int $maxHull): static
    {
        $this->maxHull = $maxHull;

        return $this;
    }

    public function getMaxShield(): int
    {
        return $this->maxShield;
    }

    public function setMaxShield(int $maxShield): static
    {
        $this->maxShield = $maxShield;

        return $this;
    }

    public function getVariableFromName(string $variableName): int
    {
        switch ($variableName) {
            case DaedalusVariableEnum::OXYGEN:
                return $this->maxOxygen;
            case DaedalusVariableEnum::FUEL:
                return $this->maxFuel;
            case DaedalusVariableEnum::HULL:
                return $this->maxHull;
            case DaedalusVariableEnum::SHIELD:
                return $this->maxShield;
            default:
                throw new \LogicException('this is not a valid daedalusVariable');
        }
    }

    public function getNbMush(): int
    {
        return $this->nbMush;
    }

    public function setNbMush(int $nbMush): static
    {
        $this->nbMush = $nbMush;

        return $this;
    }

    public function getCyclePerGameDay(): int
    {
        return $this->cyclePerGameDay;
    }

    public function setCyclePerGameDay(int $cyclePerGameDay): static
    {
        $this->cyclePerGameDay = $cyclePerGameDay;

        return $this;
    }

    public function getCycleLength(): int
    {
        return $this->cycleLength;
    }

    public function setCycleLength(int $cycleLength): static
    {
        $this->cycleLength = $cycleLength;

        return $this;
    }
}
