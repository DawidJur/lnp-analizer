<?php

namespace App\Controller;

use App\Entity\League;
use App\Entity\Player;
use App\Repository\QueueRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     */
    public function index(): Response
    {
        $teams = $this->entityManager->getRepository(Player::class)->findAll();
        $this->queueAdder->addToQueue($teams);

        return new JsonResponse('success');
    }

    /**
     * @Route("/queue/manager/{page}", name="queue_manager")
     * @param int $page
     * @return JsonResponse
     */
    public function manage(int $page): Response
    {
        $entities = $this->queueRepository->getEntities(10, $page);
        if (false === empty($entities))
            $this->queueManager->manage($entities);

        return new JsonResponse('success');
    }
}
