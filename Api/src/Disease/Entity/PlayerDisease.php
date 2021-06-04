<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\Player;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_player")
 */
class PlayerDisease
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Disease\Entity\DiseaseConfig")
     */
    private DiseaseConfig $diseaseConfig;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player")
     */
    private Player $player;

    /**
     * @ORM\Column(type="integer")
     */
    private int $diseasePoint = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->diseaseConfig;
    }

    public function setDiseaseConfig(DiseaseConfig $diseaseConfig): PlayerDisease
    {
        $this->diseaseConfig = $diseaseConfig;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): PlayerDisease
    {
        $this->player = $player;

        return $this;
    }

    public function getDiseasePoint(): int
    {
        return $this->diseasePoint;
    }

    public function setDiseasePoint(int $diseasePoint): PlayerDisease
    {
        $this->diseasePoint = $diseasePoint;

        return $this;
    }
}
