<?php

namespace App\Service\Queue;

use App\Entity\Queue;
use App\Repository\LeagueRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\Extractor\PlayersExtractor;
use App\Service\Extractor\PlayerStatisticsExtractor;
use App\Service\Extractor\TeamsExtractor;
use App\Service\Updater\LeaguesUpdater;
use App\Service\Updater\PlayerStatisticsUpdater;
use App\Service\Updater\PlayersUpdater;
use App\Service\Updater\TeamsUpdater;
use Doctrine\ORM\EntityManagerInterface;

class QueueManager
{
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
        $data = $this->extractData($arrayToExtract);

        $this->teamsUpdater->save($data[QueueEnum::TEAMS_FROM_LEAGUES]);
        $this->playersUpdater->save($data[QueueEnum::PLAYERS_FROM_TEAMS]);
        $this->playerStatisticsUpdater->save($data[QueueEnum::PLAYERS_STAT]);

        foreach ($arrayToExtract as $queue) {
            $this->entityManager->remove($queue);
        }

        $this->entityManager->flush();
    }

    private function extractData(array $arrayToExtract): array
    {
        $data = [
            QueueEnum::TEAMS_FROM_LEAGUES => [],
            QueueEnum::PLAYERS_FROM_TEAMS => [],
            QueueEnum::PLAYERS_STAT => [],
        ];

        /** @var Queue $toExtract */
        foreach ($arrayToExtract as $toExtract) {
            $type = $toExtract->getType();
            switch ($type) {
                case QueueEnum::TEAMS_FROM_LEAGUES:
                    $repository = $this->leagueRepository;
                    $extractor = $this->teamsExtractor;
                    break;
                case QueueEnum::PLAYERS_FROM_TEAMS:
                    $repository = $this->teamRepository;
                    $extractor = $this->playersExtractor;
                    break;
                case QueueEnum::PLAYERS_STAT:
                    $repository = $this->playerRepository;
                    $extractor = $this->playerStatisticsExtractor;
                    break;
                default:
                    continue 2;
            }

            $entity = $repository->findOneBy(['id' => $toExtract->getTargetId()]);
            if ($entity === null) continue;

            try {
                $data[$type] = \array_merge($extractor->extract($entity), $data[$type]);
            } catch (\Exception $e) {
                dump($e);
                die;
            }
        }

        return $data;
    }
}