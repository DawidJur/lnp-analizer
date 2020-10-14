<?php

namespace App\Service\Queue;

use App\Entity\Queue;
use App\Repository\QueueRepository;

class QueueProvider
{
    private const NUMBER_OF_REQUESTS_PLAYER_STAT_MAKES = 4;

    private QueueRepository $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function getQueueEntities(int $limit, int $page): array
    {
        return $this->queueRepository->getEntities($limit, $page);
    }

    public function getQueueEntitiesByRequestsLimit(int $requestsLimit): array
    {
        $entityNumber = $numberOfRequests = 0;
        $entities = [];
        do {
            /** @var Queue $entity */
            $entity = $this->queueRepository->getEntities(1, $entityNumber++);
            if (empty($entity)) {
                break;
            }

            $entities = array_merge($entities, $entity);

            $numberOfRequests +=
                $entity->getType() === QueueEnum::PLAYERS_STAT
                    ? self::NUMBER_OF_REQUESTS_PLAYER_STAT_MAKES
                    : 1;

        } while($numberOfRequests <= $requestsLimit);

        return $entities;
    }
}
