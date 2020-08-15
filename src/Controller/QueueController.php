<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QueueController extends AbstractController
{
    private QueueManager $queueManager;

    private QueueAdder $queueAdder;

    private TeamRepository $teamRepository;

    public function __construct(
        QueueManager $queueManager,
        QueueAdder $queueAdder,
        TeamRepository $teamRepository
    )
    {
        $this->queueManager = $queueManager;
        $this->queueAdder = $queueAdder;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @Route("/queue", name="queue")
     */
    public function index()
    {
        echo 'test'; die;
        $teams = $this->teamRepository->findAll();
        dump($teams);die;
        $this->queueAdder->addToQueue($teams);
        die;
        return $this->render('queue/index.html.twig', [
            'controller_name' => 'QueueController',
        ]);
    }

    /**
     * @Route("/queue/manager", name="queue")
     */
    public function manage()
    {


        return new JsonResponse([]);
    }
}
