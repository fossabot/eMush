<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Enum\EndCauseEnum;

/**
 * @ORM\Entity()
 */
class DeadPlayerInfo
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne (targetEntity="Mush\Player\Entity\Player")
     */
    private Player $player;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $endStatus = EndCauseEnum::NO_INFIRMERY;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $dayDeath;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cycleDeath;

    /**
     * @ORM\Column(type="array")
     */
    private ?array $likes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return static
     */
    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getDayDeath(): int
    {
        return $this->dayDeath;
    }

    /**
     * @return static
     */
    public function setDayDeath(int $dayDeath): self
    {
        $this->dayDeath = $dayDeath;

        return $this;
    }

    public function getCycleDeath(): int
    {
        return $this->cycleDeath;
    }

    /**
     * @return static
     */
    public function setCycleDeath(int $cycleDeath): self
    {
        $this->cycleDeath = $cycleDeath;

        return $this;
    }

    public function getLikes(): ?array
    {
        return $this->likes;
    }

    /**
     * @return static
     */
    public function setLikes(array $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getEndStatus(): string
    {
        return $this->endStatus;
    }

    /**
     * @return static
     */
    public function setEndStatus(string $endStatus): self
    {
        $this->endStatus = $endStatus;

        return $this;
    }
}
