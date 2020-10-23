<?php

namespace Mush\User\Service;

use Mush\User\Entity\User;

interface UserServiceInterface
{
    public function persist(User $user): User;

    public function findById(int $id): ?User;

    public function findUserByUserId(string $userId): ?User;
}
