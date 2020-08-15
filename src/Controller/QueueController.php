<?php

namespace App\Controller;

use App\Repository\QueueRepository;
use App\Repository\TeamRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QueueController extends AbstractController
{
    private QueueManager $queueManager;

    private QueueAdder $queueAdder;

    private TeamRepository $teamRepository;

    private QueueRepository $queueRepository;

    public function __construct(
        QueueManager $queueManager,
        QueueAdder $queueAdder,
        TeamRepository $teamRepository,
        QueueRepository $queueRepository
    )
    {
        $this->queueManager = $queueManager;
        $this->queueAdder = $queueAdder;
        $this->teamRepository = $teamRepository;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @Route("/queue", name="queue")
     */
    public function index(): Response
    {
        $teams = $this->teamRepository->findAll();
        $this->queueAdder->addToQueue($teams);
die;
        return $this->render('queue/index.html.twig', [
            'controller_name' => 'QueueController',
        ]);
    }

    /**
     * @Route("/queue/manager/{page}", name="queue_manager")
     * @param int $page
     * @return JsonResponse
     */
    public function manage(int $page): Response
    {
        $entities = $this->queueRepository->getEntities(50, $page);
        $this->queueManager->manage($entities);

        return new JsonResponse([]);
    }
}
