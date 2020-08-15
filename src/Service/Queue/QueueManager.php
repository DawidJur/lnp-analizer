<?php


namespace App\Service\Queue;


use App\Entity\Queue;
use App\Service\Crawler\LeaguesExtractor;
use App\Service\Crawler\PlayersExtractor;
use App\Service\Crawler\TeamsExtractor;
use App\Service\Updater\LeaguesUpdater;
use App\Service\Updater\PlayersUpdater;
use App\Service\Updater\TeamsUpdater;

class QueueManager
{
    private LeaguesExtractor $leaguesExtractor;

    private LeaguesUpdater $leaguesUpdater;

    private TeamsExtractor $teamsExtractor;

    private TeamsUpdater $teamsUpdater;

    private PlayersExtractor $playersExtractor;

    private PlayersUpdater $playersUpdater;

    public function __construct(
        LeaguesExtractor $leaguesExtractor,
        LeaguesUpdater $leaguesUpdater,
        TeamsExtractor $teamsExtractor,
        TeamsUpdater $teamsUpdater,
        PlayersExtractor $playersExtractor,
        PlayersUpdater $playersUpdater
    )
    {
        $this->leaguesExtractor = $leaguesExtractor;
        $this->leaguesUpdater = $leaguesUpdater;
        $this->teamsExtractor = $teamsExtractor;
        $this->teamsUpdater = $teamsUpdater;
        $this->playersExtractor = $playersExtractor;
        $this->playersUpdater = $playersUpdater;
    }

    public function manage(array $arrayToExtract): bool
    {
        [$leagues, $teams, $players, $playersStats] = $this->extractData($arrayToExtract);

        $this->leaguesUpdater->save($leagues);
        $this->teamsUpdater->save($teams);
        $this->playersUpdater->save($players);
        $this->playersStatsUpdater->save($playersStats);
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
                case QueueEnum::LEAGUE:
                    $leagues = array_merge($this->leaguesExtractor->extractLeaguesFromWebsite($toExtract->getLink()), $leagues);
                    break;
                case QueueEnum::TEAM:
                    $teams = array_merge($this->leaguesExtractor->extractLeaguesFromWebsite($toExtract->getLink()), $leagues);
                    break;
                case QueueEnum::PLAYER:
                    $players = array_merge($this->leaguesExtractor->extractLeaguesFromWebsite($toExtract->getLink()), $leagues);
                    break;
                case QueueEnum::PLAYER_STATS:
                    $playersStats = array_merge($this->leaguesExtractor->extractLeaguesFromWebsite($toExtract->getLink()), $leagues);
                    break;
            }
        }

        return [$leagues, $teams, $players, $playersStats];
    }
}