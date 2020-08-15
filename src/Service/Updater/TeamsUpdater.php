<?php

namespace App\Service\Updater;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;

class TeamsUpdater implements UpdaterInterface
{
    private TeamRepository $teamRepository;

    private EntityManagerInterface $entityManager;

    private const CHUNK_SIZE = 50;

    public function __construct(
        TeamRepository $teamRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->teamRepository = $teamRepository;
        $this->entityManager = $entityManager;
    }

    public function save(array $teams): int
    {
        $addedNewTeams = 0;
        $teamsLinks = $this->teamRepository->getAllLinks();
        foreach ($teams as $team) {
            if (\in_array($team['link'], $teamsLinks)) {
                continue;
            }

            $teamEntity = new Team();
            $teamEntity->setName($team['name']);
            $teamEntity->setLink($team['link']);
            $teamEntity->setLeague($team['league']);
            $this->entityManager->persist($teamEntity);

            $addedNewTeams++;

            if ($addedNewTeams % self::CHUNK_SIZE === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return $addedNewTeams;
    }
}