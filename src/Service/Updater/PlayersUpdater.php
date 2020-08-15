<?php


namespace App\Service\Updater;


use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlayersUpdater implements UpdaterInterface
{
    private PlayerRepository $playerRepository;

    private EntityManagerInterface $entityManager;

    private const CHUNK_SIZE = 50;

    public function __construct(
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
    }

    public function save(array $players): int
    {
        $addedNewPlayers = 0;
        foreach ($players as $player) {
            $playerEntity = $this->playerRepository->findOneBy(['link' => $player['link']]); //todo optimize this to send only 1 query
            if (!$playerEntity) {
                $playerEntity = new Player();
                $playerEntity->setFirstName($player['firstname']);
                $playerEntity->setLastName($player['lastname']);
                $playerEntity->setLink($player['link']);
            } elseif (\in_array($player['team'], $playerEntity->getTeams()->toArray())) {
                continue;
            }

            $playerEntity->addTeam($player['team']);
            $this->entityManager->persist($playerEntity);

            $addedNewPlayers++;

            if ($addedNewPlayers % self::CHUNK_SIZE === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return $addedNewPlayers;
    }
}