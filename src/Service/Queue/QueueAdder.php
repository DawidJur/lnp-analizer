<?php


namespace App\Service\Queue;

use App\Entity\PageLinkInterface;
use App\Entity\Queue;
use App\Repository\QueueRepository;
use Doctrine\ORM\EntityManagerInterface;

class QueueAdder
{
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

    public function addToQueueArray(array $entities): void
    {
        /** @var PageLinkInterface $entity */
        foreach ($entities as $entity) {
            $type = QueueEnum::getEntityType($entity);
            try {
                $this->addToQueue($entity, $type);
            } catch (\Exception $e) {
                dump($e);
            }
        }
    }

    public function addToQueue(PageLinkInterface $entity, int $type = null): void
    {
        $queue = new Queue();
        $queue->setTargetId($entity->getId());
        $queue->setType($type);

        $this->entityManager->persist($queue);
        $this->entityManager->flush();
    }
}