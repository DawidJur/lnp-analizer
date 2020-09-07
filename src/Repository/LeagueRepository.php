<?php

namespace App\Repository;

use App\Entity\League;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
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

    public function setMarkedInGivenLeagues(array $ids): void
    {
        $this->createQueryBuilder('l')
            ->update('App:League', 'l')
            ->set('l.isMarked', 0)
            ->getQuery()
            ->execute();

        $this->createQueryBuilder('l')
            ->update('App:League', 'l')
            ->set('l.isMarked', 1)
            ->where('l.id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->execute();
    }

    public function findLeagueByNameAndPlayer(string $name, Player $player): ?League
    {
        return $this->createQueryBuilder('l')
            ->select('l')
            ->innerJoin('l.teams', 't')
            ->innerJoin('t.players', 'p')
            ->andWhere('p = :player')
            ->andWhere('UPPER(l.name) = :name')
            ->setParameter('player', $player)
            ->setParameter('name', \mb_strtoupper($name))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
