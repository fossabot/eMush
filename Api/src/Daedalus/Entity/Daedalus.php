<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

/**
 * Class Daedalus.
 *
 * @ORM\Entity(repositoryClass="Mush\Daedalus\Repository\DaedalusRepository")
 */
class Daedalus
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Player\Entity\Player", mappedBy="daedalus")
     */
    private Collection $players;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Game\Entity\GameConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Room\Entity\Room", mappedBy="daedalus")
     */
    private Collection $rooms;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $oxygen;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $fuel;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $hull;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $day = 1;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cycle = 1;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $shield;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $spores=4;

    /**
     * Daedalus constructor.
     *
     * @param int $id
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayers(): PlayerCollection
    {
        return new PlayerCollection($this->players->toArray());
    }

    public function setPlayers(Collection $players): Daedalus
    {
        $this->players = $players;

        return $this;
    }

    public function addPlayer(Player $player): Daedalus
    {
        if (!$this->getPlayers()->contains($player)) {
            if ($player->getDaedalus() !== $this) {
                $player->setDaedalus(null);
            }

            $this->players->add($player);

            $player->setDaedalus($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): Daedalus
    {
        $this->players->removeElement($player);
        if ($player->getDaedalus() === $this) {
            $player->setDaedalus(null);
        }

        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): Daedalus
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function getRoomByName(string $name): Room
    {
        return $this->getRooms()->filter(fn (Room $room) => $room->getName() === $name);
    }

    public function setRooms(Collection $rooms): Daedalus
    {
        $this->rooms = $rooms;

        return $this;
    }

    public function addRoom(Room $room): Daedalus
    {
        if (!$this->getRooms()->contains($room)) {
            if ($room->getDaedalus() !== $this) {
                $room->setDaedalus(null);
            }

            $this->rooms->add($room);

            $room->setDaedalus($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): Daedalus
    {
        $this->rooms->removeElement($room);
        if ($room->getDaedalus() === $this) {
            $room->setDaedalus(null);
        }

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    public function setOxygen(int $oxygen): Daedalus
    {
        $this->oxygen = $oxygen;

        return $this;
    }

    public function addOxygen(int $change): Daedalus
    {
        $this->oxygen = $this->oxygen + $change;

        return $this;
    }

    public function getFuel(): int
    {
        return $this->fuel;
    }

    public function setFuel(int $fuel): Daedalus
    {
        $this->fuel = $fuel;

        return $this;
    }

    public function addFuel(int $change): Daedalus
    {
        $this->fuel = $this->fuel + $change;
        
        return $this;
    }

    public function getHull(): int
    {
        return $this->hull;
    }

    public function setHull(int $hull): Daedalus
    {
        $this->hull = $hull;

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): Daedalus
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): Daedalus
    {
        $this->day = $day;

        return $this;
    }

    public function getShield(): int
    {
        return $this->shield;
    }

    public function setShield(int $shield): Daedalus
    {
        $this->shield = $shield;

        return $this;
    }

    public function getSpores(): int
    {
        return $this->spores;
    }

    public function setSpores(int $spores): Daedalus
    {
        $this->spores = $spores;

        return $this;
    }
}
