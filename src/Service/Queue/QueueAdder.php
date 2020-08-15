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
        $queueTargets = $this->queueRepository->getAllTargetEntities();
        /** @var PageLinkInterface $entity */
        foreach ($entities as $entity) {
            $type = QueueEnum::getEntityType($entity);
            if (false === empty($queueTargets) && \in_array($entity->getId(), $queueTargets[$type])) {
                continue;
            }

            $queue = new Queue();
            $queue->setTargetId($entity->getId());
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