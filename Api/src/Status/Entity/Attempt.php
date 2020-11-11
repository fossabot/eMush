<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Attempt.
 *
 * @ORM\Entity()
 */
class Attempt extends Status
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $action = null;

    protected ?int $charge = 0;

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): Attempt
    {
        $this->action = $action;

        return $this;
    }
}
