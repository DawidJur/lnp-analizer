<?php

namespace App\Controller;

use App\Repository\LeagueRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerStatisticsRepository;
use App\Repository\TeamRepository;
use App\Service\Extractor\LeaguesExtractor;
use App\Service\Extractor\PlayersExtractor;
use App\Service\Extractor\PlayerStatisticsExtractor;
use App\Service\Extractor\TeamsExtractor;
use App\Service\Updater\LeaguesUpdater;
use App\Service\Updater\PlayersUpdater;
use App\Service\Updater\TeamsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrawlerController extends AbstractController
{
    private LeaguesExtractor $leaguesExtractor;

    private LeaguesUpdater $leaguesUpdater;

    private LeagueRepository $leagueRepository;

    private TeamsExtractor $teamsExtractor;

    private TeamsUpdater $teamsUpdater;

    private TeamRepository $teamRepository;

    private PlayersExtractor $playersExtractor;

    private PlayersUpdater $playersUpdater;

    private PlayerRepository $playerRepository;

    private PlayerStatisticsExtractor $playerStatisticsExtractor;

    private PlayerStatisticsRepository $playerStatisticsRepository;

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
        PlayerStatisticsRepository $playerStatisticsRepository,
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
        $this->playerStatisticsRepository = $playerStatisticsRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="crawler")
     */
    public function index(): Response
    {
        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }

    /**
     * @Route("/crawler/leagues", name="crawler_leagues")
     */
    public function getLeagues(): Response
    {
        $leagues = $this->leaguesExtractor->extract();
        $this->leaguesUpdater->save($leagues);

        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }

    /**
     * @Route("/crawler/player/{playerId}", name="crawler_players")
     * @param int $playerId
     */
    public function getPlayersStatistics(int $playerId): Response
    {
        $player = $this->playerRepository->findOneBy(['id' => $playerId]);
        $stats = $this->playerStatisticsExtractor->extractPlayerStats($player);
        dump($stats); die;
    }
}
