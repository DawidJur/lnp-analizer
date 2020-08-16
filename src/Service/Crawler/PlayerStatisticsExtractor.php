<?php


namespace App\Service\Crawler;


use App\Entity\Player;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class PlayerStatisticsExtractor extends ExtractorAbstract implements ExtractorInterface
{
    private const SEASONS = [
        '2020-2021',
        '2019-2020',
        '2018-2019'
    ];

    private PlayerRepository $playerRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->playerRepository = $playerRepository;
        $this->entityManager = $entityManager;
    }

    public function getPlayersStats(array $players)
    {
        $playersStats = [];

        foreach ($players as $player) {
            if (false === $player instanceof Player) {
                continue;
            }

            $playersStats[] = $this->extractPlayerStats($player);
        }

        return $playersStats;
    }

    public function extractPlayerStats(Player $player)
    {
        $playerStats = [];
        $urls = $this->getUrls($player->getLink());
        foreach ($urls as $url) {
            $html = $this->getWebsiteContent($url);
            $crawler = new Crawler($html);
            $season = $crawler->filter('.seasons__page .season__period')->text();
            $playerStats[$season] = $crawler->filter('.seasons__page .season__games .season__game')->each(function (Crawler $node, $i) {
                return $node;
            });
        }


        $playersData = [];
        /** @var Crawler $playerStat */
        foreach ($playerStats as $season => $playerMatch) {
            foreach ($playerMatch as $playerStat) {
                $playersData[] = [
                    'date' => $this->extractDate($playerStat),
                    'time' => $this->extractTimePlayed($playerStat),
                    'goals' => $this->extractGoals($playerStat, $player),
                    'season' => $season
            ];
            }
        }
dump($this->getAge($player));
        $player->setAge(
            $this->getAge($player)
        );

        return ['player' => $player, 'stats' => $this->adjustPlayerStats($playersData)];
    }

    private function getAge(Player $player): int
    {
        $url = $player->getLink();
        $html = $this->getWebsiteContent($url);
        $crawler = new Crawler($html);

        return (int) $crawler->filter('.profile-page .player .about-player span')->nextAll()->text();
    }

    private function getUrls(string $link): array
    {
        $url = \explode('zawodnik/', $link)[1];
        $url = \explode('.', $url)[0];
        $url = \explode(',', $url);

        $urls = [];
        foreach (self::SEASONS as $season) {
            $urls[] = 'https://www.laczynaspilka.pl/zawodnik-sezon/' . $url[0] . ',' . $season . ',' . $url[1] . '.html';
        }

        return $urls;
    }

    private function extractTimePlayed(Crawler $crawler): int
    {
        $text = $crawler->filter('.season__game-time')->text();
        $time = explode('Minuty', $text);
        if (false === empty($time[1])) {
            $time = explode('-', $time[1]);

            return (int) $time[1] - (int) $time[0];
        }

        return 0;
    }

    private function extractGoals(Crawler $crawler, Player $player): ?int
    {
        $gameProgress = $crawler->filter('.tooltip-game .info')->each(function (Crawler $node, $i) {
            return $node;
        });

        $goals = 0;
        /** @var Crawler $gameAction */
        foreach ($gameProgress as $gameAction) {
            $html = $gameAction->outerHtml();
            if (str_contains($html, 'i-goal-small') && str_contains($html, $player->getFirstName()) && str_contains($html, $player->getLastName())) {
                $goals++;
            }
        }

        return $goals;
    }

    private function extractDate(Crawler $crawler): string
    {
        return $crawler->filter('.season__game .hour')->text() . ':00 ' .
            $crawler->filter('.season__game .day')->text() . '/' .
            $crawler->filter('.season__game .month')->text();
    }

    private function adjustPlayerStats(array $playerStats): array
    {
        foreach ($playerStats as $key => $playerStat) {
            if (0 === $playerStat['time'] && 0 === $playerStat['goals']) {
                unset($playerStats[$key]);
            }
        }

        return $playerStats;
    }
}