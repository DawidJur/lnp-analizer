<?php

namespace App\Repository;

use App\Entity\League;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method League|null find($id, $lockMode = null, $lockVersion = null)
 * @method League|null findOneBy(array $criteria, array $orderBy = null)
 * @method League[]    findAll()
 * @method League[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeagueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, League::class);
    }

    public function getAllLinks(): array
    {
        return \array_column($this->createQueryBuilder('l')
            ->select('l.link')
            ->getQuery()
            ->getResult(), 'link')
            ;
    }

    public function getRandomLeagues(): array
    {
        $connection =  $this->getEntityManager()->getConnection();
        $statement = $connection->prepare("SELECT id FROM League ORDER BY RAND() LIMIT 10");
        $statement->execute();

        return array_column($statement->fetchAll(), 'id');
    }
}
