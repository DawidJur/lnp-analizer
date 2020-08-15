<?php

namespace App\Repository;

use App\Entity\Queue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Queue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Queue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Queue[]    findAll()
 * @method Queue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Queue::class);
    }

    public function getAllTargetEntities(): array
    {
        /** @var Queue[] $queues */
        $queues = $this->createQueryBuilder('q')
            ->select('q')
            ->getQuery()
            ->getResult();


        $queueArray = [];
        //todo rewrite to array map
        foreach ($queues as $queue) {
            $queueArray[$queue->getType()][] = $queue->getTargetId();
        }

        return $queueArray;
    }

    public function getEntities(int $numberOfEntities, int $page): array
    {
        return $this->createQueryBuilder('q')
            ->select('q')
            ->setMaxResults($numberOfEntities)
            ->setFirstResult($numberOfEntities * $page)
            ->getQuery()
            ->getResult();
    }
}
