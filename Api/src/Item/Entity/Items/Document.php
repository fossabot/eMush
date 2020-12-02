<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item.
 *
 * @ORM\Entity
 */
class Document extends Tool
{
    protected string $type = ItemTypeEnum::DOCUMENT;

    protected array $actions = [ActionEnum::READ_DOCUMENT, ActionEnum::SHRED];

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

    public function setContent(string $content): Document
    {
        $this->content = $content;

        return $this;
    }

    public function IsTranslated(): bool
    {
        return $this->isTranslated;
    }

    public function setIsTranslated(bool $isTranslated): Document
    {
        $this->isTranslated = $isTranslated;

        return $this;
    }

    public function canShred(): bool
    {
        return $this->canShred;
    }

    public function setCanShred(bool $canShred): Document
    {
        $this->canShred = $canShred;

        return $this;
    }

}
