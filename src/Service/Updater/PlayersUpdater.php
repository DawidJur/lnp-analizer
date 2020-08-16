<?php


namespace App\Service\Updater;


use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Service\Queue\QueueAdder;
use App\Service\Queue\QueueEnum;
use Doctrine\ORM\EntityManagerInterface;

class PlayersUpdater implements UpdaterInterface
{
    private PlayerRepository $playerRepository;

    private EntityManagerInterface $entityManager;

    private QueueAdder $queueAdder;

    public function __construct(
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager,
        QueueAdder $queueAdder
    )
    {
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
        $this->queueAdder = $queueAdder;
    }

    public function save(array $players): void
    {
        foreach ($players as $player) {
            $playerEntity = $this->playerRepository->findOneBy(['link' => $player['link']]); //todo optimize this to send only 1 query
            if (!$playerEntity) {
                $playerEntity = new Player();
                $playerEntity->setFirstName($player['firstname']);
                $playerEntity->setLastName($player['lastname']);
                $playerEntity->setLink($player['link']);
            } elseif (\in_array($player['team'], $playerEntity->getTeams()->toArray())) {
                $this->entityManager->flush(); //flush changed age
                continue;
            }

            $playerEntity->addTeam($player['team']);
            $this->entityManager->persist($playerEntity);
            $this->entityManager->flush();

            $this->queueAdder->addToQueue($playerEntity, QueueEnum::PLAYERS_STAT);
        }
    }
}