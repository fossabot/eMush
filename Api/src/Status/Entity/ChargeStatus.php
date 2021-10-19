<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChargeStatus.
 *
 * @ORM\Entity
 */
class ChargeStatus extends Status
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $chargeVisibility;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $charge = 0;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $strategy = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $dischargeStrategy = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $threshold = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $autoRemove = false;

    public function getChargeVisibility(): string
    {
        return $this->chargeVisibility;
    }

    /**
     * @return static
     */
    public function setChargeVisibility(string $chargeVisibility): self
    {
        $this->chargeVisibility = $chargeVisibility;

        return $this;
    }

    public function getCharge(): int
    {
        return $this->charge;
    }

    /**
     * @return static
     */
    public function addCharge(int $charge): self
    {
        $this->charge += $charge;

        return $this;
    }

    /**
     * @return static
     */
    public function setCharge(int $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    /**
     * @return static
     */
    public function setStrategy(?string $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getDischargeStrategy(): ?string
    {
        return $this->dischargeStrategy;
    }

    /**
     * @return static
     */
    public function setDischargeStrategy(?string $dischargeStrategy): self
    {
        $this->dischargeStrategy = $dischargeStrategy;

        return $this;
    }

    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    /**
     * @return static
     */
    public function setThreshold(?int $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function isAutoRemove(): bool
    {
        return $this->autoRemove;
    }

    /**
     * @return static
     */
    public function setAutoRemove(bool $autoRemove): self
    {
        $this->autoRemove = $autoRemove;

        return $this;
    }
}
