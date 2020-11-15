<?php

namespace Mush\Room\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Door;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;

/**
 * Class Room.
 *
 * @ORM\Entity(repositoryClass="Mush\Room\Repository\RoomRepository")
 */
class Room
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="rooms")
     */
    private ?Daedalus $daedalus = null;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Player\Entity\Player", mappedBy="room")
     */
    private Collection $players;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Item\Entity\GameItem", mappedBy="room")
     */
    private Collection $items;

    /**
     * @ORM\ManyToMany (targetEntity="Mush\Item\Entity\Door", cascade={"persist"}, orphanRemoval=true)
     */
    private Collection $doors;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $statuses;

    /**
     * Room constructor.
     *
     * @param int $id
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->doors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Room
    {
        $this->name = $name;

        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(?Daedalus $daedalus): Room
    {
        if ($daedalus !== $this->daedalus) {
            $oldDaedalus = $this->daedalus;
            $this->daedalus = $daedalus;

            if ($daedalus !== null) {
                $daedalus->addRoom($this);
            }

            if ($oldDaedalus !== null) {
                $oldDaedalus->removeRoom($this);
            }
        }

        return $this;
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function setPlayers(ArrayCollection $players): Room
    {
        $this->players = $players;

        return $this;
    }

    public function addPlayer(Player $player): Room
    {
        if (!$this->getPlayers()->contains($player)) {
            if ($player->getRoom() !== $this) {
                $player->setRoom(null);
            }

            $this->players->add($player);

            $player->setRoom($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): Room
    {
        $this->players->removeElement($player);
        if ($player->getRoom() === $this) {
            $player->setRoom(null);
        }

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(ArrayCollection $items): Room
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(GameItem $item): Room
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setRoom($this);
        }

        return $this;
    }

    public function removeItem(GameItem $item): Room
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $item->setRoom(null);
        }

        return $this;
    }

    public function getDoors(): Collection
    {
        return $this->doors;
    }

    public function setDoors(ArrayCollection $doors): Room
    {
        $this->doors = $doors;

        return $this;
    }

    public function addDoor(Door $door): Room
    {
        $this->doors->add($door);
        $door->addRoom($this);

        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): Room
    {
        $this->statuses = $statuses;

        return $this;
    }
}
