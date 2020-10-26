<?php

namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

/**
 * Class ItemConfig
 * @package Mush\Item\Entity
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "blue_print" = "BluePrint",
 *     "book" = "Book",
 *     "component" = "Component",
 *     "document" = "Document",
 *     "drug" = "Drug",
 *     "entity" = "Entity",
 *     "exploration" = "Exploration",
 *     "fruit" = "Fruit",
 *     "gear" = "Gear",
 *     "instrument" = "Instrument",
 *     "misc" = "Misc",
 *     "plant" = "Plant",
 *     "ration" = "Ration",
 *     "tool" = "Tool",
 *     "weapon" = "Weapon"
 * })
 */
abstract class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="itemsConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    private string $type;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isHeavy;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isDismantable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isStackable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isHideable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isMovable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isTakeable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
     private bool $isDropable;

     /**
      * @ORM\Column(type="boolean", nullable=false)
      */

    private bool $isFireDestroyable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireBreakable;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $actions;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $effects;

    public function createGameItem(): GameItem
    {
        $gameItem = new GameItem();
        $gameItem
            ->setName($this->getName())
            ->setItem($this)
        ;

        return $gameItem;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): Item
    {
        $this->gameConfig = $gameConfig;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Item
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Item
    {
        $this->type = $type;
        return $this;
    }

    public function isHeavy(): bool
    {
        return $this->isHeavy;
    }

    public function setIsHeavy(bool $isHeavy): Item
    {
        $this->isHeavy = $isHeavy;
        return $this;
    }

    public function isDropable(): bool
    {
        return $this->isDropable;
    }

    public function setIsDropable(bool $isDropable): Item
    {
        $this->isDropable = $isDropable;
        return $this;
    }

    public function isDismantable(): bool
    {
        return $this->isDismantable;
    }

    public function setIsDismantable(bool $isDismantable): Item
    {
        $this->isDismantable = $isDismantable;
        return $this;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    public function setIsStackable(bool $isStackable): Item
    {
        $this->isStackable = $isStackable;
        return $this;
    }

    public function isHideable(): bool
    {
        return $this->isHideable;
    }

    public function setIsHideable(bool $isHideable): Item
    {
        $this->isHideable = $isHideable;
        return $this;
    }

    public function isMovable(): bool
    {
        return $this->isMovable;
    }

    public function setIsMovable(bool $isMovable): Item
    {
        $this->isMovable = $isMovable;
        return $this;
    }

    public function isTakeable(): bool
    {
        return $this->isTakeable;
    }

    public function setIsTakeable(bool $isTakeable): Item
    {
        $this->isTakeable = $isTakeable;
        return $this;
    }

    public function isFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    public function setIsFireDestroyable(bool $isFireDestroyable): Item
    {
        $this->isFireDestroyable = $isFireDestroyable;
        return $this;
    }

    public function isFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    public function setIsFireBreakable(bool $isFireBreakable): Item
    {
        $this->isFireBreakable = $isFireBreakable;
        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): Item
    {
        $this->actions = $actions;
        return $this;
    }

    public function addAction(string $action, array $effect = []): Item
    {
        if (!$this->hasAction($action))
        {
            $this->actions[] = $action;
            if ($effect !== []) {
              $this->effects[$action] = $effect;
            }
        }

        return $this;
    }

    public function removeAction(string $action): Item
    {
        $this->actions = array_diff($this->getActions(), [$action]);
        if (array_key_exists($this->effects, $action))
        {
          $this->effects = array_diff($this->getEffect(), $this->getEffect($action));
        }
        return $this;
    }

    public function hasAction(string $actions): bool
    {
        return in_array($actions, $this->getActions());
    }

    public function getEffect(string $action): array
    {
        return $this->effects[$action] ?? [];
    }
}
