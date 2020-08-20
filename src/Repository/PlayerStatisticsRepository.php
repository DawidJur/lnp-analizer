<?php

namespace App\Repository;

use App\Entity\PlayerStatistics;
use App\Service\PlayersList\PlayersFormResolver;
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
    private PlayersFormResolver $playersFormResolver;

    public function __construct(
        ManagerRegistry $registry,
        PlayersFormResolver $playersFormResolver
    )
    {
        parent::__construct($registry, PlayerStatistics::class);
        $this->playersFormResolver = $playersFormResolver;
    }

    public function getPlayersWithStats(array $filters = []): array
    {
        dump($filters);
        $qb = $this->playersFormResolver->resolve($filters);
dump($qb->getQuery()->getSQL());
        return $qb->getQuery()
            ->execute();
    }
}
