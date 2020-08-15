<?php

namespace App\Service\Crawler;

use App\Entity\League;
use Symfony\Component\DomCrawler\Crawler;

class TeamsExtractor extends ExtractorAbstract implements ExtractorInterface
{
    public function getTeams(array $leagues): array
    {
        $teams = [];
        /** @var League $league */
        foreach ($leagues as $league) {
            $teams = \array_merge($this->extractTeamsFromLeague($league), $teams);
        }

        return \array_unique($teams, SORT_REGULAR);
    }

    public function extractTeamsFromLeague(League $league): array
    {
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