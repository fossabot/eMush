<?php

namespace Mush\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;

interface ModifierServiceInterface
{
    public function persist(Modifier $modifier): Modifier;

    public function delete(Modifier $modifier): void;

    public function createModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        string $cause,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment,
        ?ChargeStatus $chargeStatus = null
    ): void;

    public function deleteModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment
    ): void;

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int;

    public function consumeActionCharges(Action $action, Player $player, ?LogParameterInterface $parameter): void;

    public function getEventModifiedValue(
        ModifierHolder $holder,
        array $scopes,
        string $target,
        int $initValue,
        string $reason,
        bool $consumeCharge = true
    ): int;

    public function playerEnterRoom(Player $player): void;

    public function playerLeaveRoom(Player $player): void;
}
