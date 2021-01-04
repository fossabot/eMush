<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Document extends Tool
{
    protected string $mechanic = EquipmentMechanicEnum::DOCUMENT;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $content;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isTranslated = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $canShred = false;

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return static
     */
    public function setContent(string $content): Document
    {
        $this->content = $content;

        return $this;
    }

    public function isTranslated(): bool
    {
        return $this->isTranslated;
    }

    /**
     * @return static
     */
    public function setIsTranslated(bool $isTranslated): Document
    {
        $this->isTranslated = $isTranslated;

        return $this;
    }

    public function canShred(): bool
    {
        return $this->canShred;
    }

    /**
     * @return static
     */
    public function setCanShred(bool $canShred): Document
    {
        $this->canShred = $canShred;

        return $this;
    }
}
