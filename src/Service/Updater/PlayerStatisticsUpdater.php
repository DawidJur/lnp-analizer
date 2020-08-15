<?php

namespace App\Service\Updater;

use App\Entity\PlayerStatistics;
use Doctrine\ORM\EntityManagerInterface;

class PlayerStatisticsUpdater implements UpdaterInterface
{
    private EntityManagerInterface $entityManager;

    private const CHUNK_SIZE = 50;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function save(array $data): int
    {
        $statsAdded = 0;
        foreach ($data as $playerData) {
            $playerData['player']->removeAllPlayerStatistic();
            foreach ($playerData['stats'] as $stat) {
                $statsAdded++;
                if ($statsAdded % self::CHUNK_SIZE === 0) $this->entityManager->flush();

                $playerStat = new PlayerStatistics();
                $playerStat->setType(1);
                $playerStat->setValue($stat['time']);
                $playerStat->setSeason($stat['season']);
                $playerStat->setDate(\DateTime::createFromFormat('H:i:s d/m/Y', $stat['date'], 'Europe/Warsaw'));
                $playerData['player']->addPlayerStatistic($playerStat);
                if (0 === $stat['goals']) {
                    continue;
                }

                $playerStat = new PlayerStatistics();
                $playerStat->setType(1);
                $playerStat->setValue($stat['goals']);
                $playerStat->setSeason($stat['season']);
                $playerStat->setDate(\DateTime::createFromFormat('H:i:s d/m/Y', $stat['date'], 'Europe/Warsaw'));
                $playerData['player']->addPlayerStatistic($playerStat);
            }

            try {
                $this->entityManager->flush();
            } catch (\Exception $e) {

            }
        }

        $this->entityManager->flush();

        return $statsAdded;
    }
}