<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\User\Entity\User;

interface AdminServiceInterface
{
    public function isGameInMaintenance(): bool;

    public function putGameInMaintenance(): void;

    public function removeGameFromMaintenance(): void;
}
