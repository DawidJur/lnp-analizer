<?php

namespace App\Service\Queue;

use App\Entity\League;
use App\Entity\PageLinkEntityInterface;
use App\Entity\Player;
use App\Entity\Team;

class QueueEnum
{
    public const TEAMS_FROM_LEAGUES = 1;

    public const PLAYERS_FROM_TEAMS = 2;

    public const PLAYERS_STAT = 3;

    public static function getEntityType(PageLinkEntityInterface $entity): ?int
    {
        if ($entity instanceof League) {
            return self::TEAMS_FROM_LEAGUES;
        }
        if ($entity instanceof Team) {
            return self::PLAYERS_FROM_TEAMS;
        }
        if ($entity instanceof Player) {
            return self::PLAYERS_STAT;
        }

        return null;
    }
}