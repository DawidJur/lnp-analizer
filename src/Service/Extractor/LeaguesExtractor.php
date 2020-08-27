<?php


namespace App\Service\Extractor;


use Symfony\Component\DomCrawler\Crawler;

class LeaguesExtractor extends ExtractorAbstract
{
    private const API_URL = 'https://www.laczynaspilka.pl/league/get_lower';

    public function extract(): array
    {
        $leagues = [];
        try {
            $leagues = $this->getLeagues();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $leagues;
    }

    private function getLeagues(): array
    {
        //only Silesia, ID 18
        return array_merge(
            $this->extractLeaguesFromWebsite('https://www.laczynaspilka.pl/league/get_lower?&zpn_id[0]=18&mode=&season=2020%2F2021&juniors=1'),
            $this->extractLeaguesFromWebsite('https://www.laczynaspilka.pl/league/get_lower?&zpn_id[0]=18&mode=&season=2020%2F2021'),

            /*$this->extractLeaguesFromWebsite('https://www.laczynaspilka.pl/league/get_lower?&zpn_id[0]=18&mode=&season=2019%2F2020&juniors=1'),
            $this->extractLeaguesFromWebsite('https://www.laczynaspilka.pl/league/get_lower?&zpn_id[0]=18&mode=&season=2019%2F2020'),

            $this->extractLeaguesFromWebsite('https://www.laczynaspilka.pl/league/get_lower?&zpn_id[0]=18&mode=&season=2018%2F2019&juniors=1'),
            $this->extractLeaguesFromWebsite('https://www.laczynaspilka.pl/league/get_lower?&zpn_id[0]=18&mode=&season=2018%2F2019'),*/

        );
/*
        $leagues = [];
        for ($i = 0; $i < 2; $i++) {
            switch ($i) {
                case 1:
                    $parameter = '&juniors=1';
                default:
                    $parameter = null;
            }

            for ($id = 1; $id < 19; $id++) {
                $url = self::API_URL . '?zpn_id[0]=' . $id . $parameter;
                $apiContent = $this->getWebsiteContent($url);
                if (false === $this->validateIfLeaguePageExists($apiContent)) {
                    continue;
                }

                $crawler = new Crawler(json_decode($apiContent)->leagues);
                $leagues = array_merge($this->extractLeaguesFromWebsite($crawler), $leagues);
            }
        }


        if (empty($leagues)) {
            throw new \Exception('No leagues found');
        }

        return $leagues;*/
    }

    public function extractLeaguesFromWebsite(string $url)
    {
        $crawler = new Crawler(
            \json_decode(
                $this->getWebsiteContent($url)
            )->leagues
        );

        $leaguesData = [];
        $leagueLists = $crawler->filter('.box-list li')->each(function (Crawler $node, $i) {
            return $node;
        });
        /** @var Crawler $leagueList */
        foreach ($leagueLists as $leagueList) {
            $link = $leagueList->filter('a');
            $leaguesData[] = [
                'name' => $link->text(),
                'link' => $link->link()->getUri()
            ];
        }

        return $leaguesData;
    }

    public function extractNameFromGivenUrl(string $url): ?string
    {
        $html = $this->getWebsiteContent($url . '?round=0');
        $crawler = new Crawler($html);
        $nameWithSeason = $crawler->filter('.box-standard .season-games__nav .name-year')->text();

        return explode(' - ', $nameWithSeason)[0];
    }
}