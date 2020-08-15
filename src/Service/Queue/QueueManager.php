<?php

namespace App\Service\Queue;

use App\Entity\Queue;
use App\Repository\LeagueRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\Crawler\LeaguesExtractor;
use App\Service\Crawler\PlayersExtractor;
use App\Service\Crawler\PlayerStatisticsExtractor;
use App\Service\Crawler\TeamsExtractor;
use App\Service\Updater\LeaguesUpdater;
use App\Service\Updater\PlayerStatisticsUpdater;
use App\Service\Updater\PlayersUpdater;
use App\Service\Updater\TeamsUpdater;
use Doctrine\ORM\EntityManagerInterface;

class QueueManager
{
    private LeaguesExtractor $leaguesExtractor;

    private LeaguesUpdater $leaguesUpdater;

    private LeagueRepository $leagueRepository;

    private TeamsExtractor $teamsExtractor;

    private TeamsUpdater $teamsUpdater;

    private TeamRepository $teamRepository;

    private PlayersExtractor $playersExtractor;

    private PlayersUpdater $playersUpdater;

    private PlayerStatisticsExtractor $playerStatisticsExtractor;

    private PlayerStatisticsUpdater $playerStatisticsUpdater;

    private PlayerRepository $playerRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        LeaguesExtractor $leaguesExtractor,
        LeaguesUpdater $leaguesUpdater,
        LeagueRepository $leagueRepository,
        TeamsExtractor $teamsExtractor,
        TeamsUpdater $teamsUpdater,
        TeamRepository $teamRepository,
        PlayersExtractor $playersExtractor,
        PlayersUpdater $playersUpdater,
        PlayerRepository $playerRepository,
        PlayerStatisticsExtractor $playerStatisticsExtractor,
        PlayerStatisticsUpdater $playerStatisticsUpdater,
        EntityManagerInterface $entityManager
    )
    {
        $this->leaguesExtractor = $leaguesExtractor;
        $this->leaguesUpdater = $leaguesUpdater;
        $this->leagueRepository = $leagueRepository;
        $this->teamsExtractor = $teamsExtractor;
        $this->teamsUpdater = $teamsUpdater;
        $this->teamRepository = $teamRepository;
        $this->playersExtractor = $playersExtractor;
        $this->playersUpdater = $playersUpdater;
        $this->playerRepository = $playerRepository;
        $this->playerStatisticsExtractor = $playerStatisticsExtractor;
        $this->playerStatisticsUpdater = $playerStatisticsUpdater;
        $this->entityManager = $entityManager;
    }

    public function manage(array $arrayToExtract): void
    {
        [$leagues, $teams, $players, $playersStats] = $this->extractData($arrayToExtract);

        $this->leaguesUpdater->save($leagues);
        $this->teamsUpdater->save($teams);
        $this->playersUpdater->save($players);
        $this->playerStatisticsUpdater->save($playersStats);

        foreach ($arrayToExtract as $queue) {
            $this->entityManager->remove($queue);
        }

        $this->entityManager->flush();
    }

    private function extractData(array $arrayToExtract): array
    {
        $leagues = [];
        $teams = [];
        $players = [];
        $playersStats = [];

        /** @var Queue $toExtract */
        foreach ($arrayToExtract as $toExtract) {
            switch ($toExtract->getType()) {
                case QueueEnum::TEAMS_FROM_LEAGUES:
                    $league = $this->leagueRepository->findOneBy(['id' => $toExtract->getId()]);
                    if (null === $league) continue;
                    $teams = \array_merge($this->teamsExtractor->extractTeamsFromLeague($league), $teams);
                    break;
                case QueueEnum::PLAYERS_FROM_TEAMS:
                    $team = $this->teamRepository->findOneBy(['id' => $toExtract->getId()]);
                    if (null === $team) continue;
                    $players = \array_merge($this->playersExtractor->extractPlayersFromTeam($team), $players);
                    break;
                case QueueEnum::PLAYERS_STAT:
                    $player = $this->playerRepository->findOneBy(['id' => $toExtract->getId()]);
                    if (null === $player) continue;
                    $playersStats = \array_merge($this->playerStatisticsExtractor->extractPlayerStats($player), $playersStats);
                    break;
            }
        }

        return [$leagues, $teams, $players, $playersStats];
    }
}