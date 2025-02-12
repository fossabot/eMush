<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Status\Entity\ChargeStatus;

interface ModifierCreationServiceInterface
{
    public function persist(GameModifier $modifier): GameModifier;

    public function delete(GameModifier $modifier): void;

    public function createModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        array $tags,
        \DateTime $time,
        ChargeStatus $chargeStatus = null
    ): void;

    public function deleteModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        array $tags,
        \DateTime $time,
    ): void;
}
