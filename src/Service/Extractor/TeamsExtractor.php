<?php

namespace App\Service\Extractor;

use App\Entity\League;
use App\Entity\PageLinkEntityInterface;
use Symfony\Component\DomCrawler\Crawler;

class TeamsExtractor extends ExtractorAbstract implements ExtractorInterface
{
    public function extract(PageLinkEntityInterface $league): array
    {
        if (false === $league instanceof League) {
            throw new \Exception('Wrong implementation');
        }

        $url = $this->returnTeamsInLeagueUrl($league->getLink());
        $html = $this->getWebsiteContent($url);
        $crawler = new Crawler($html);
        $teams = $crawler->filter('.season__games .season__game-name .teams a.team')->each(function (Crawler $node, $i) {
            return $node;
        });

        $teamsData = [];
        /** @var Crawler $team */
        foreach ($teams as $team) {
            $teamsData[] = [
                'name' => $team->text(),
                'link' => $team->link()->getUri(),
                'league' => $league
            ];
        }

        return \array_unique($teamsData, SORT_REGULAR);
    }

    private function returnTeamsInLeagueUrl(string $league): string
    {
        return $league . '?round=0';
    }
}