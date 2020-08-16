<?php

namespace App\Controller;

use App\Entity\League;
use App\Entity\Player;
use App\Entity\Team;
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
    {//378,385 in db 
        $leagues = $this->entityManager->getRepository(League::class)->findAll();


        return $this->render('crawler/index.html.twig', [
            'controller_name' => 'CrawlerController',
        ]);
    }

    public function newLeague(): Response
    {

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
