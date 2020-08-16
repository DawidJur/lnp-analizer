<?php

namespace App\Service\Updater;

use App\Entity\League;
use App\Repository\LeagueRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueEnum;
use Doctrine\ORM\EntityManagerInterface;

class LeaguesUpdater implements UpdaterInterface
{
    private EntityManagerInterface $entityManager;

    private LeagueRepository $leagueRepository;

    private QueueAdder $queueAdder;

    public function __construct(
        EntityManagerInterface $entityManager,
        LeagueRepository $leagueRepository,
        QueueAdder $queueAdder
    ) {
        $this->entityManager = $entityManager;
        $this->leagueRepository = $leagueRepository;
        $this->queueAdder = $queueAdder;
    }

    public function save(array $leagues): void
    {
        $leaguesLinks = $this->leagueRepository->getAllLinks();

        foreach ($leagues as $league) {
            if (\in_array($league['link'], $leaguesLinks)) {
                continue;
            }

            $leagueEntity = new League();
            $leagueEntity->setName($league['name']);
            $leagueEntity->setLink($league['link']);
            $this->entityManager->persist($leagueEntity);
            $this->entityManager->flush();
            $this->queueAdder->addToQueue($leagueEntity, QueueEnum::TEAMS_FROM_LEAGUES);
        }
    }
}