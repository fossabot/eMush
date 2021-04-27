<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

/**
 * Class GameItem.
 *
 * @ORM\Entity
 */
class GameItem extends GameEquipment
{
    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player", inversedBy="items")
     */
    private ?Player $player = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param  \Mush\Player\Entity\Player|null  $player
     *
     * @return self
     */
    public function setPlayer(?Player $player): GameItem
    {
        if ($player !== $this->getPlayer()) {
            $oldPlayer = $this->getPlayer();

            $this->player = $player;

            if ($player !== null) {
                $player->addItem($this);
            }

            if ($oldPlayer !== null) {
                $oldPlayer->removeItem($this);
            }
        }

        if ($player === null && $this->player !== null) {
            $this->player->removeItem($this);
        }

        if ($player !== null && $this->player !== $player) {
            $player->addItem($this);
        }

        $this->player = $player;

        return $this;
    }

    /**
     * @return self
     */
    public function removeLocation(): GameItem
    {
        $this->setPlace(null);
        $this->setPlayer(null);

        return $this;
    }

    public function getCurrentPlace(): Place
    {
        if ($player = $this->getPlayer()) {
            $room = $player->getPlace();
        } else {
            $room = $this->getPlace();
        }

        if ($room === null) {
            throw new \LogicException('Cannot find room of game item');
        }

        return $room;
    }
}
