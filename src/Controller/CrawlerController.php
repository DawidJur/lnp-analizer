<?php

namespace App\Controller;

use App\Repository\LeagueRepository;
use App\Repository\TeamRepository;
use App\Service\Crawler\LeaguesExtractor;
use App\Service\Crawler\PlayersExtractor;
use App\Service\Crawler\TeamsExtractor;
use App\Service\Updater\LeaguesUpdater;
use App\Service\Updater\PlayersUpdater;
use App\Service\Updater\TeamsUpdater;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="crawler")
     */
    public function index()
    {
        $leagues = $this->leagueRepository->findAll();
        dump($leagues);
        die;
        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }

    /**
     * @Route("/crawler/leagues", name="crawler_leagues")
     */
    public function getLeagues()
    {
        $leagues = $this->leaguesExtractor->extract();
        $addedNewLeagues = $this->leaguesUpdater->save($leagues);
        dump($addedNewLeagues);

        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }

    /**
     * @Route("/crawler/teams", name="crawler_teams")
     */
    public function getTeams()
    {
        $leagues = $this->leagueRepository->findAll();
        //$leagues = $this->leagueRepository->findBy(['id' => $this->leagueRepository->getRandomLeagues()]);
        //$leagues = $this->leagueRepository->findBy(['link' => 'https://www.laczynaspilka.pl/rozgrywki/nizsze-ligi,38303.html']);
        $teams = $this->teamsExtractor->getTeams($leagues);
        $addedNewTeams = $this->teamsUpdater->save($teams);
        dump($addedNewTeams);
        dump($teams);

        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }

    /**
     * @Route("/crawler/players", name="crawler_players")
     */
    public function getPlayers()
    {
        //$teams = $this->teamRepository->findAll();
        $teams = $this->teamRepository->findBy(['id' => $this->teamRepository->getRandomTeams()]);
        $players = $this->playersExtractor->getPlayers($teams);
        dump($players);
        $addedNewPlayers = $this->playersUpdater->save($players);
        dump($addedNewPlayers);

        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }
}
