<?php


namespace App\Service\Queue;

use App\Entity\Queue;
use Doctrine\ORM\EntityManagerInterface;

class QueueAdder
{
    private const CHUNK_SIZE = 50;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addToQueue(array $entities): void
    {
        $addedToQueue = 0;
        foreach ($entities as $entity) {
            $type = QueueEnum::getEntityType($entity);
            $queue = new Queue();
            $queue->setLink($entity->getLink());
            $queue->setType($type);

            $this->entityManager->persist($queue);

            $addedToQueue++;
            if ($addedToQueue % self::CHUNK_SIZE === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
    }
}