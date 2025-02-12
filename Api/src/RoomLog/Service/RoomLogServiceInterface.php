<?php

namespace Mush\RoomLog\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogServiceInterface
{
    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        Player $player = null,
        array $parameters = [],
        \DateTime $dateTime = null
    ): RoomLog;

    public function createLogFromActionResult(
        string $actionName,
        ActionResult $actionResult,
        Player $player,
        ?LogParameterInterface $actionParameter,
        \DateTime $time
    ): ?RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;

    public function getRoomLog(Player $player): RoomLogCollection;
}
