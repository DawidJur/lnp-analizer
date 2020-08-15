<?php


namespace App\Service\Queue;

use App\Entity\PageLinkInterface;
use App\Entity\Queue;
use App\Repository\QueueRepository;
use Doctrine\ORM\EntityManagerInterface;

class QueueAdder
{
    private const CHUNK_SIZE = 50;

    private EntityManagerInterface $entityManager;

    private QueueRepository $queueRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        QueueRepository $queueRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->queueRepository = $queueRepository;
    }

    public function addToQueue(array $entities): void
    {
        $addedToQueue = 0;
        $links = $this->queueRepository->getAllLinks();
        /** @var PageLinkInterface $entity */
        foreach ($entities as $entity) {
            if (in_array($entity->getLink(), $links)) {
                continue;
            }

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