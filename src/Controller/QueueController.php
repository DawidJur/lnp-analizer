<?php

namespace App\Controller;

use App\Entity\League;
use App\Entity\Queue;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueManager;
use App\Service\Queue\QueueProvider;
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

    private QueueProvider $queueProvider;

    private EntityManagerInterface $entityManager;

    public function __construct(
        QueueManager $queueManager,
        QueueAdder $queueAdder,
        QueueProvider $queueProvider,
        EntityManagerInterface $entityManager
    )
    {
        $this->queueManager = $queueManager;
        $this->queueAdder = $queueAdder;
        $this->queueProvider = $queueProvider;
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
        $entities = $this->queueProvider->getQueueEntities(15, $page);
        if (empty($entities))
            return new JsonResponse('no queue entities found');

        $this->queueManager->manage($entities);

        return new JsonResponse('success');
    }

    /**
     * @Route("/queue/manager/v2/{limit}", name="queue_manager_v2")
     * @param int $limit
     * @return JsonResponse
     */
    public function manageLimit(int $limit): Response
    {
        $entities = $this->queueProvider->getQueueEntitiesByRequestsLimit($limit);
        if (empty($entities))
            return new JsonResponse('no queue entities found');

        $this->queueManager->manage($entities);

        return new JsonResponse('success');
    }
}
