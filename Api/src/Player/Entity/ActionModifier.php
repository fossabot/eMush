<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ActionModifier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $movementPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $healthPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $moralPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $satietyModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $precisionModifier = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getActionPointModifier(): int
    {
        return $this->actionPointModifier;
    }

    /**
     * @return static
     */
    public function setActionPointModifier(int $actionPointModifier): ActionModifier
    {
        $this->actionPointModifier = $actionPointModifier;

        return $this;
    }

    public function getMovementPointModifier(): int
    {
        return $this->movementPointModifier;
    }

    /**
     * @return static
     */
    public function setMovementPointModifier(int $movementPointModifier): ActionModifier
    {
        $this->movementPointModifier = $movementPointModifier;

        return $this;
    }

    public function getHealthPointModifier(): int
    {
        return $this->healthPointModifier;
    }

    /**
     * @return static
     */
    public function setHealthPointModifier(int $healthPointModifier): ActionModifier
    {
        $this->healthPointModifier = $healthPointModifier;

        return $this;
    }

    public function getMoralPointModifier(): int
    {
        return $this->moralPointModifier;
    }

    /**
     * @return static
     */
    public function setMoralPointModifier(int $moralPointModifier): ActionModifier
    {
        $this->moralPointModifier = $moralPointModifier;

        return $this;
    }

    public function getSatietyModifier(): int
    {
        return $this->satietyModifier;
    }

    /**
     * @return static
     */
    public function setSatietyModifier(int $satietyModifier): ActionModifier
    {
        $this->satietyModifier = $satietyModifier;

        return $this;
    }

    public function getPrecisionModifier(): int
    {
        return $this->precisionModifier;
    }

    /**
     * @return static
     */
    public function setPrecisionModifier(int $precisionModifier): ActionModifier
    {
        $this->precisionModifier = $precisionModifier;

        return $this;
    }
}
