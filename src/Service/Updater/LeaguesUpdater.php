<?php


namespace App\Service\Updater;


use App\Entity\League;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;

class LeaguesUpdater implements UpdaterInterface
{
    private EntityManagerInterface $entityManager;

    private LeagueRepository $leagueRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LeagueRepository $leagueRepository
    ) {
        $this->entityManager = $entityManager;
        $this->leagueRepository = $leagueRepository;
    }

    public function save(array $leagues): int
    {
        $addedNewLeagues = 0;
        $leaguesLinks = $this->leagueRepository->getAllLinks();

        foreach ($leagues as $league) {
            if (\in_array($league['link'], $leaguesLinks)) {
                continue;
            }

            $leagueEntity = new League();
            $leagueEntity->setName($league['name']);
            $leagueEntity->setLink($league['link']);
            $this->entityManager->persist($leagueEntity);

            $addedNewLeagues++;
        }

        $this->entityManager->flush();

        return $addedNewLeagues;
    }
}