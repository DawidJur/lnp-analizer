<?php

namespace App\Controller;

use App\Entity\League;
use App\Form\FindPlayersType;
use App\Form\NewLeagueType;
use App\Repository\LeagueRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerStatisticsRepository;
use App\Repository\TeamRepository;
use App\Service\Extractor\LeaguesExtractor;
use App\Service\Extractor\PlayersExtractor;
use App\Service\Extractor\PlayerStatisticsExtractor;
use App\Service\Extractor\TeamsExtractor;
use App\Service\Queue\QueueAdder;
use App\Service\Updater\LeaguesUpdater;
use App\Service\Updater\PlayersUpdater;
use App\Service\Updater\TeamsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    private QueueAdder $queueAdder;

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
        EntityManagerInterface $entityManager,
        QueueAdder $queueAdder
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
        $this->queueAdder = $queueAdder;
    }

    /**
     * @Route("/", name="list")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(FindPlayersType::class);
        $form->handleRequest($request);
        $data = $this->playerStatisticsRepository->getPlayersWithStats($form->getData() ?? []);

        return $this->render('crawler/index.html.twig', [
            'form' => $form->createView(),
            'data' => $data,
        ]);
    }

    /**
     * @Route("/crawler/league", name="crawler_league")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function getLeague(Request $request): Response
    {
        $form = $this->createForm(NewLeagueType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = \trim($form->getData()['newLeagueUrl']);

            $name = $this->leaguesExtractor->extractNameFromGivenUrl($url);
            if (!$name) {
                throw new \Exception('Nastąpił nieoczekiwany błąd. Być może podany link jest niepoprawny.');
            }

            if ($this->leagueRepository->findOneBy(['link' => $url])) {
                throw new \Exception('Ta liga jest już w bazie');
            }

            $league = new League();
            $league->setLink($url);
            $league->setName($name);
            $this->entityManager->persist($league);
            $this->entityManager->flush();
            $this->queueAdder->addToQueue($league, 1);

            return $this->redirectToRoute('queue');
        }

        return $this->render('crawler/league_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/crawler/init", name="crawler_leagues_from_api")
     */
    public function getLeaguesFromApi(): Response
    {
        $leagues = $this->leaguesExtractor->extract();
        $this->leaguesUpdater->save($leagues);

        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }
}
