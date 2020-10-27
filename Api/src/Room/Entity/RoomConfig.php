<?php


namespace Mush\Room\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\DaedalusConfig;

/**
 * Class RoomConfig
 * @package Mush\Room\Entity
 * @ORM\Entity()
 */
class RoomConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\DaedalusConfig", inversedBy="roomConfigs")
     */
    private DaedalusConfig $daedalusConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $doors = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $items = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalusConfig(): DaedalusConfig
    {
        return $this->daedalusConfig;
    }

    public function setDaedalusConfig(DaedalusConfig $daedalusConfig): RoomConfig
    {
        $this->daedalusConfig = $daedalusConfig;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RoomConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getDoors(): array
    {
        return $this->doors;
    }

    public function setDoors(array $doors): RoomConfig
    {
        $this->doors = $doors;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): RoomConfig
    {
        $this->items = $items;
        return $this;
    }
}
