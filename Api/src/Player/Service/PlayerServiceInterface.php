<?php

namespace Mush\Player\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;

interface PlayerServiceInterface
{
    public function persist(Player $player): Player;

    public function findById(int $id): ?Player;

    public function findOneByCharacter(string $character, Daedalus $daedalus): ?Player;

    public function createPlayer(Daedalus $daedalus, User $user, string $character): Player;

    public function handleNewCycle(Player $player, \DateTime $date): Player;

    public function handleNewDay(Player $player, \DateTime $date): Player;

    public function findUserCurrentGame(User $user): ?Player;

    public function playerDeath(Player $player, ?string $reason, \DateTime $time): Player;
}
