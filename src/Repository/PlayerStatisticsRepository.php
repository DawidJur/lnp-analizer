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
}
