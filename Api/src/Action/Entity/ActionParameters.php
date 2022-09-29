<?php

namespace Mush\Action\Entity;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class ActionParameters
{
    private ?Place $place = null;
    private ?Player $player = null;
    private ?Equipment $equipment = null;
    private ?Item $item = null;
    private ?Door $door = null;
    private string $message = '';

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getDoor(): ?Door
    {
        return $this->door;
    }

    public function setDoor(?Door $door): static
    {
        $this->door = $door;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
