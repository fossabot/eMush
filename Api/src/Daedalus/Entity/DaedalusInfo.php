<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;

#[ORM\Entity]
#[ORM\Table(name: 'daedalus_info')]
class DaedalusInfo
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: GameConfig::class)]
    private GameConfig $gameConfig;

    #[ORM\OneToOne(inversedBy: 'daedalusInfo', targetEntity: Daedalus::class, cascade: ['ALL'])]
    private ?Daedalus $daedalus;

    #[ORM\OneToOne(inversedBy: 'daedalusInfo', targetEntity: ClosedDaedalus::class, cascade: ['ALL'])]
    private ClosedDaedalus $closedDaedalus;

    #[ORM\OneToOne(inversedBy: 'daedalusInfo', targetEntity: Neron::class, cascade: ['ALL'])]
    private Neron $neron;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $gameStatus = GameStatusEnum::STANDBY;

    #[ORM\ManyToOne(targetEntity: LocalizationConfig::class)]
    private LocalizationConfig $localizationConfig;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name = 'default';

    public function __construct(Daedalus $daedalus, GameConfig $gameConfig, LocalizationConfig $localizationConfig)
    {
        $this->daedalus = $daedalus;
        $this->gameConfig = $gameConfig;
        $this->localizationConfig = $localizationConfig;

        $daedalus->setDaedalusInfo($this);
        $closedDaedalus = $this->closedDaedalus = new ClosedDaedalus();
        $closedDaedalus->setDaedalusInfo($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function getClosedDaedalus(): ClosedDaedalus
    {
        return $this->closedDaedalus;
    }

    public function setClosedDaedalus(ClosedDaedalus $closedDaedalus): static
    {
        $this->closedDaedalus = $closedDaedalus;

        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): static
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getNeron(): Neron
    {
        return $this->neron;
    }

    public function setNeron(Neron $neron): static
    {
        $this->neron = $neron;
        $neron->setDaedalusInfo($this);

        return $this;
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

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function setGameStatus(string $gameStatus): static
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function setLocalizationConfig(LocalizationConfig $localizationConfig): self
    {
        $this->localizationConfig = $localizationConfig;

        return $this;
    }

    public function getLocalizationConfig(): LocalizationConfig
    {
        return $this->localizationConfig;
    }
}
