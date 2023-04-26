<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Game\Entity\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'disease_consummable_config')]
class ConsumableDiseaseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $causeName;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $diseasesName = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $curesName = [];

    // Store the chance (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $diseasesChances;

    // Store the chance (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $curesChances;

    // Store the min delay (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $diseasesDelayMin;

    // Store the max delay (value) for the disease to appear (key)
    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $diseasesDelayLength;

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $effectNumber;

    #[ORM\OneToMany(targetEntity: ConsumableDiseaseAttribute::class, mappedBy: 'consumableDiseaseConfig', cascade: ['persist'])]
    private Collection $consumableAttributes;

    public function __construct()
    {
        $this->consumableAttributes = new ArrayCollection();
        $this->diseasesChances = new ProbaCollection();
        $this->curesChances = new ProbaCollection();
        $this->diseasesDelayMin = new ProbaCollection();
        $this->diseasesDelayLength = new ProbaCollection();
        $this->effectNumber = new ProbaCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCauseName(): string
    {
        return $this->causeName;
    }

    public function setCauseName(string $causeName): self
    {
        $this->causeName = $causeName;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function appendConfigKeyToName(string $configName): self
    {
        $this->name = $this->causeName . '_' . $configName;

        return $this;
    }

    public function getDiseasesName(): array
    {
        return $this->diseasesName;
    }

    public function setDiseasesName(array $diseasesName): self
    {
        $this->diseasesName = $diseasesName;

        return $this;
    }

    public function getCuresName(): array
    {
        return $this->curesName;
    }

    public function setCuresName(array $curesName): self
    {
        $this->curesName = $curesName;

        return $this;
    }

    public function getDiseasesChances(): ProbaCollection
    {
        return $this->diseasesChances;
    }

    public function setDiseasesChances(array $diseasesChances): self
    {
        $this->diseasesChances = new ProbaCollection($diseasesChances);

        return $this;
    }

    public function getCuresChances(): ProbaCollection
    {
        return $this->curesChances;
    }

    public function setCuresChances(array $curesChances): self
    {
        $this->curesChances = new ProbaCollection($curesChances);

        return $this;
    }

    public function getDiseasesDelayMin(): ProbaCollection
    {
        return $this->diseasesDelayMin;
    }

    public function setDiseasesDelayMin(array $diseasesDelayMin): self
    {
        $this->diseasesDelayMin = new ProbaCollection($diseasesDelayMin);

        return $this;
    }

    public function getDiseasesDelayLength(): ProbaCollection
    {
        return $this->diseasesDelayLength;
    }

    public function setDiseasesDelayLength(array $diseasesDelayLength): self
    {
        $this->diseasesDelayLength = new ProbaCollection($diseasesDelayLength);

        return $this;
    }

    public function getEffectNumber(): ProbaCollection
    {
        return $this->effectNumber;
    }

    public function setEffectNumber(array $effectNumber): self
    {
        $this->effectNumber = new ProbaCollection($effectNumber);

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->consumableAttributes;
    }

    /**
     * @psalm-param ArrayCollection<int, ConsumableDiseaseAttribute> $diseases
     */
    public function setAttributes(Collection $diseases): self
    {
        $this->consumableAttributes = $diseases;

        return $this;
    }

    public function addDisease(ConsumableDiseaseAttribute $disease): self
    {
        if (!$this->consumableAttributes->contains($disease)) {
            $this->consumableAttributes->add($disease);
        }

        return $this;
    }
}
