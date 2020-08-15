<?php

namespace App\Repository;

use App\Entity\PlayerStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerStatistics[]    findAll()
 * @method PlayerStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerStatistics::class);
    }

    // /**
    //  * @return PlayerStatistics[] Returns an array of PlayerStatistics objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlayerStatistics
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
