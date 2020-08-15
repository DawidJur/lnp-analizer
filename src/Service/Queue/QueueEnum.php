<?php

namespace App\Service\Queue;

use App\Entity\League;
use App\Entity\PageLinkInterface;
use App\Entity\Player;
use App\Entity\Team;

class QueueEnum
{
    public const LEAGUE = 1;

    public const TEAM = 2;

    public const PLAYER = 3;

    public static function getEntityType(PageLinkInterface $entity): int
    {
        if ($entity instanceof League) {
            return self::LEAGUE;
        }
        if ($entity instanceof Team) {
            return self::TEAM;
        }
        if ($entity instanceof Player) {
            return self::PLAYER;
        }

        return 0;
    }
}