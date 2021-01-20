<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;

/**
 * Class GameEquipment.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "game_equipment" = "Mush\Equipment\Entity\GameEquipment",
 *     "door" = "Mush\Equipment\Entity\Door",
 *     "game_item" = "Mush\Equipment\Entity\GameItem"
 * })
 */
class GameEquipment implements StatusHolderInterface
{
    use TimestampableEntity;
    use TargetStatusTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Status\Entity\StatusTarget", mappedBy="gameEquipment", cascade="ALL")
     */
    private Collection $statuses;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Room\Entity\Room", inversedBy="equipments")
     */
    private ?Room $room = null;

    /**
     * @ORM\ManyToOne(targetEntity="EquipmentConfig")
     */
    private EquipmentConfig $equipment;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * GameEquipment constructor.
     */
    public function __construct()
    {
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getActions(): Collection
    {
        return $this->equipment->getActions();
    }

    /**
     * @return static
     */
    public function addStatus(Status $status): self
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setGameEquipment($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    /**
     * @return static
     */
    public function setRoom(?Room $room): GameEquipment
    {
        if ($room !== $this->room) {
            $oldRoom = $this->getRoom();
            $this->room = $room;

            if ($room !== null) {
                $room->addEquipment($this);
            }

            if ($oldRoom !== null) {
                $oldRoom->removeEquipment($this);
                $this->room = $room;
            }
        }

        return $this;
    }

    public function getCurrentRoom(): Room
    {
        if ($this->room === null) {
            throw new \LogicException('Cannot find room of game equipment');
        }

        return $this->room;
    }

    /**
     * @return static
     */
    public function removeLocation(): GameEquipment
    {
        $this->setRoom(null);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): GameEquipment
    {
        $this->name = $name;

        return $this;
    }

    public function getEquipment(): EquipmentConfig
    {
        return $this->equipment;
    }

    /**
     * @return static
     */
    public function setEquipment(EquipmentConfig $equipment): GameEquipment
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function isBroken(): bool
    {
        return $this
            ->getStatuses()
            ->exists(fn (int $key, Status $status) => ($status->getName() === EquipmentStatusEnum::BROKEN))
            ;
    }

    public function getBrokenRate(): int
    {
        return $this->getEquipment()->getBreakableRate();
    }
}
