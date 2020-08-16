<?php

namespace App\Service\Updater;

use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueEnum;
use Doctrine\ORM\EntityManagerInterface;

class TeamsUpdater implements UpdaterInterface
{
    private TeamRepository $teamRepository;

    private EntityManagerInterface $entityManager;

    private QueueAdder $queueAdder;

    public function __construct(
        TeamRepository $teamRepository,
        EntityManagerInterface $entityManager,
        QueueAdder $queueAdder
    ) {
        $this->teamRepository = $teamRepository;
        $this->entityManager = $entityManager;
        $this->queueAdder = $queueAdder;
    }

    public function save(array $teams): void
    {
        foreach ($teams as $team) {
            $teamEntity = $this->teamRepository->findOneBy(['link' => $team['link']]);
            if ($teamEntity) {
                $this->queueAdder->addToQueue($teamEntity, QueueEnum::PLAYERS_FROM_TEAMS);
                continue;
            }

            $teamEntity = new Team();
            $teamEntity->setName($team['name']);
            $teamEntity->setLink($team['link']);
            $teamEntity->setLeague($team['league']);
            $this->entityManager->persist($teamEntity);
            $this->entityManager->flush();

            $this->queueAdder->addToQueue($teamEntity, QueueEnum::PLAYERS_FROM_TEAMS);
        }
    }
}