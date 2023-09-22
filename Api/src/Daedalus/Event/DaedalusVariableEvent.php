<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;

class DaedalusVariableEvent extends DaedalusEvent implements VariableEventInterface
{
    private float $quantity;
    private string $variableName;

    public function __construct(
        Daedalus $daedalus,
        string $variableName,
        float $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->variableName = $variableName;
        $this->quantity = $quantity;

        parent::__construct($daedalus, $tags, $time);
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getRoundedQuantity(): int
    {
        return intval($this->quantity);
    }

    public function getVariable(): GameVariable
    {
        return $this->daedalus->getVariableByName($this->variableName);
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }
}
