<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Player\Entity\Player;

class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findOneByName(string $name, Daedalus $daedalus): ?Player
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->leftJoin(CharacterConfig::class, 'character_config', Join::WITH, 'user.characterConfig = character_config')
            ->where($qb->expr()->eq('character_config.name', ':name'))
            ->andWhere($qb->expr()->eq('user.daedalus', ':daedalus'))
            ->setParameter('name', $name)
            ->setParameter('daedalus', $daedalus)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
