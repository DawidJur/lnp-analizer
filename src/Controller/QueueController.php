<?php

namespace App\Controller;

use App\Entity\League;
use App\Entity\Queue;
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
        $entities = $this->queueRepository->getEntities(30, $page);
        if (empty($entities))
            return new JsonResponse('no queue entities found');

        $this->queueManager->manage($entities);

        return new JsonResponse('success');
    }
}
