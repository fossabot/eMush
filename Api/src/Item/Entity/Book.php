<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Book extends Tool
{
    protected array $actions = []; // @Todo: read action

    private string $skill;

    public function getSkill(): string
    {
        return $this->skill;
    }

    public function setSkill(string $skill): Book
    {
        $this->skill = $skill;
        return $this;
    }
}
