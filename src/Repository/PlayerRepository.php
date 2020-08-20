<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function getAllLinks(): array
    {
        return \array_column($this->createQueryBuilder('p')
            ->select('p.link')
            ->getQuery()
            ->getResult(), 'link')
            ;
    }

    public function relation(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p, t, l')
            ->innerJoin('p.teams', 't')
            ->innerJoin('t.league', 'l')
            ->setMaxResults(1)
            ->andWhere('l.id = 11')
            ->getQuery()
            ->execute();
    }
}
