<?php

namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ItemType.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "blue_print" = "Mush\Item\Entity\Items\BluePrint",
 *     "book" = "Mush\Item\Entity\Items\Book",
 *     "component" = "Mush\Item\Entity\Items\Component",
 *     "document" = "Mush\Item\Entity\Items\Document",
 *     "drug" = "Mush\Item\Entity\Items\Drug",
 *     "entity" = "Mush\Item\Entity\Items\Entity",
 *     "exploration" = "Mush\Item\Entity\Items\Exploration",
 *     "fruit" = "Mush\Item\Entity\Items\Fruit",
 *     "gear" = "Mush\Item\Entity\Items\Gear",
 *     "instrument" = "Mush\Item\Entity\Items\Instrument",
 *     "misc" = "Mush\Item\Entity\Items\Misc",
 *     "plant" = "Mush\Item\Entity\Items\Plant",
 *     "ration" = "Mush\Item\Entity\Items\Ration",
 *     "tool" = "Mush\Item\Entity\Items\Tool",
 *     "weapon" = "Mush\Item\Entity\Items\Weapon",
 *     "dismountable" = "Mush\Item\Entity\Items\Dismountable",
 *     "charged" = "Mush\Item\Entity\Items\Charged"
 * })
 */
abstract class ItemType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    protected string $type;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected array $actions = [];

    public function initItem(GameItem $gameItem): GameItem
    {
        return $gameItem;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): ItemType
    {
        $this->actions = $actions;

        return $this;
    }
}
