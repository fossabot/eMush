<?php

namespace Mush\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername(string $username): ?User
    {
        $user = $this->findOneBy(['userId' => $username]);

        return $user instanceof User ? $user : null;
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $user = $this->findOneBy(['userId' => $identifier]);

        return $user instanceof User ? $user : null;
    }

    public function findUserDaedaluses(User $user): array
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->select('daedalus_info')
            ->innerJoin(PlayerInfo::class, 'player_info', 'WITH', 'player_info.user = user.id')
            ->innerJoin('player_info.player', 'player')
            ->innerJoin('player.daedalus', 'daedalus')
            ->innerJoin(DaedalusInfo::class, 'daedalus_info', 'WITH', 'daedalus_info.id = daedalus.id')
            ->where($qb->expr()->eq('user.id', ':user_id'))
            ->setParameter('user_id', $user->getId())
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUserCurrentPlayer(User $user): ?PlayerInfo
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->select('player_info')
            ->innerJoin(PlayerInfo::class, 'player_info', 'WITH', 'player_info.user = user.id')
            ->where($qb->expr()->eq('user.id', ':user_id'))
            ->andWhere($qb->expr()->in('player_info.gameStatus', ':game_status'))
            ->setParameter('user_id', $user->getId())
            ->setParameter('game_status', [GameStatusEnum::CURRENT, GameStatusEnum::FINISHED])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
