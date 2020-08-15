<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function getAllLinks(): array
    {
        return \array_column($this->createQueryBuilder('l')
            ->select('l.link')
            ->getQuery()
            ->getResult(), 'link')
            ;
    }

    public function getRandomTeams(): array
    {
        $connection =  $this->getEntityManager()->getConnection();
        $statement = $connection->prepare("SELECT id FROM team ORDER BY RAND() LIMIT 100");
        $statement->execute();

        return array_column($statement->fetchAll(), 'id');
    }
}
