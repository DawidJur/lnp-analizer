<?php

namespace App\Service\Extractor;

use App\Entity\PageLinkEntityInterface;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class PlayersExtractor extends ExtractorAbstract implements ExtractorInterface
{
    private PlayerRepository $playerRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
    }

    public function extract(PageLinkEntityInterface $team): array
    {
        if (false === $team instanceof Team) {
            throw new \Exception('Wrong implementation');
        }

        $url = $team->getLink();
        $html = $this->getWebsiteContent($url);
        $crawler = new Crawler($html);
        $players = $crawler->filter('.team-box .players-list a.item')->each(function (Crawler $node, $i) {
            return $node;
        });

        $playersData = [];
        /** @var Crawler $player */
        foreach ($players as $player) {
            $playersData[] = [
                'firstname' => $player->filter('.name')->text(),
                'lastname' => $player->filter('.surname')->text(),
                'link' => $player->link()->getUri(),
                'team' => $team
            ];
        }

        return \array_unique($playersData, SORT_REGULAR);
    }
}