<?php

namespace Mush\RoomLog\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogServiceInterface
{
    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        ?Player $targetPlayer = null,
        ?GameEquipment $targetEquipment = null,
        ?int $quantity = null,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createLogFromActionResult(string $actionName, ActionResult $actionResult, Player $player): ?RoomLog;

    public function getMessageParam(
        ?Player $player = null,
        ?Player $targetPlayer = null,
        ?GameEquipment $targetEquipment = null,
        ?int $quantity = null,
    ): array;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;

    public function getRoomLog(Player $player): array;
}
