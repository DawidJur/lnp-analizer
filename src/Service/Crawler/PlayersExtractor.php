<?php


namespace App\Service\Crawler;


use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class PlayersExtractor extends ExtractorAbstract implements ExtractorInterface
{
    private $playerRepository;

    private $entityManager;

    public function __construct(
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
    }

    public function getPlayers(array $teams)
    {
        $players = [];

        foreach ($teams as $team) {
            if (false === $team instanceof Team) {
                continue;
            }

            $players = \array_merge($this->extractPlayersFromTeam($team), $players);
        }

        return \array_map("unserialize", \array_unique(\array_map("serialize", $players)));
    }

    public function extractPlayersFromTeam(Team $team)
    {
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
                'surname' => $player->filter('.surname')->text(),
                'link' => $player->link()->getUri(),
                'team' => $team
            ];
        }

        return \array_map("unserialize", \array_unique(\array_map("serialize", $playersData)));
    }
}