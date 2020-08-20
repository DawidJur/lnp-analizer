<?php

namespace App\Controller;

use App\Entity\League;
use App\Entity\LeagueQueue;
use App\Entity\Player;
use App\Entity\Queue;
use App\Entity\Team;
use App\Form\LeaguesQueueCollectionType;
use App\Repository\QueueRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QueueController extends AbstractController
{
    private QueueManager $queueManager;

    private QueueAdder $queueAdder;

    private QueueRepository $queueRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        QueueManager $queueManager,
        QueueAdder $queueAdder,
        QueueRepository $queueRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->queueManager = $queueManager;
        $this->queueAdder = $queueAdder;
        $this->queueRepository = $queueRepository;
        $this->entityManager = $entityManager;
    }

/*SELECT s.player_id, p.first_name as 'imie', p.last_name as 'nazwisko', p.age as 'wiek', sum(s.value) wartosc, s.season FROM `player_statistics` s
inner join player p on p.id = s.player_id
where p.age >= 14 and p.age < 18
and season = 'SEZON 2019/2020'
GROUP By s.player_id, s.type, s.season
ORDER BY s.type DESC, wartosc DESC*/

    /**
     * @Route("/queue", name="queue")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $leagueMarked = $request->request->get('addLeagueToQueue');
        if (false === empty($leagueMarked)) {
            $this->entityManager->getRepository(League::class)->setMarkedInGivenLeagues($leagueMarked);
            $leagues = $this->entityManager->getRepository(League::class)->findBy(['id' => $leagueMarked]);
            $this->queueAdder->addToQueueArray($leagues);
        }

        $leagues = $this->entityManager->getRepository(League::class)->findAll();
        $queueCount = $this->entityManager->getRepository(Queue::class)->count([]);

        return $this->render('queue/index.html.twig', [
            'queueCount' => $queueCount,
            'leagues' => $leagues
        ]);
    }

    /**
     * @Route("/queue/manager/{page}", name="queue_manager")
     * @param int $page
     * @return JsonResponse
     */
    public function manage(int $page): Response
    {
        $entities = $this->queueRepository->getEntities(15, $page);
        if (empty($entities))
            return new JsonResponse('no queue entities found');

        $this->queueManager->manage($entities);

        return new JsonResponse('success');
    }
}
