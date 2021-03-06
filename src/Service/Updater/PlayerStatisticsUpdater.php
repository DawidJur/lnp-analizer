<?php

namespace App\Service\Updater;

use App\Entity\PlayerStatistics;
use Doctrine\ORM\EntityManagerInterface;

class PlayerStatisticsUpdater implements UpdaterInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function save(array $data): void
    {
        foreach ($data as $playerData) {
            $playerData['player']->removeAllPlayerStatistics();
            foreach ($playerData['stats'] as $stat) {
                if ($stat['time']) {
                    $playerStat = new PlayerStatistics();
                    $playerStat->setType(1);
                    $playerStat->setValue($stat['time']);
                    $playerStat->setSeason($stat['season']);
                    $playerStat->setLeague($stat['league']);
                    $playerStat->setDate(\DateTime::createFromFormat('H:i:s d/m/Y', $stat['date']));
                    $this->entityManager->persist($playerStat);
                    $playerData['player']->addPlayerStatistic($playerStat);
                }

                if ($stat['goals']) {
                    $playerStat = new PlayerStatistics();
                    $playerStat->setType(2);
                    $playerStat->setValue($stat['goals']);
                    $playerStat->setSeason($stat['season']);
                    $playerStat->setLeague($stat['league']);
                    $playerStat->setDate(\DateTime::createFromFormat('H:i:s d/m/Y', $stat['date']));
                    $this->entityManager->persist($playerStat);
                    $playerData['player']->addPlayerStatistic($playerStat);
                }
            }

            $this->entityManager->flush();
        }
    }
}