<?php

namespace App\Service\PlayersList;

use App\Entity\PlayerStatistics;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PlayersFormResolver
{
    private EntityManagerInterface $em;

    private const NAME = 'CONCAT(p.firstName, \' \', p.lastName)';

    private int $subQueryCount = 0;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->em = $entityManager;
    }

    public function resolve(array $filters): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder();
        $this->prepare($qb, $filters);
        $this->resolveName($qb, $filters);
        $this->resolveAge($qb, $filters);
        $this->resolveGoals($qb, $filters);
        $this->resolveMinutes($qb, $filters);
        $this->resolveLeague($qb, $filters);
        $this->resolveSeason($qb, $filters);
        $this->resolvePagination($qb, $filters);
        $this->resolveDate($qb, $filters);
        $this->resolveOrderBy($qb, $filters);

        return $qb;
    }

    private function prepare(QueryBuilder $qb, array $filters): void
    {
        $qb
            ->from('App:Player', 'p')
            ->addSelect('p.id')
            ->addSelect(self::NAME . ' as name')
            ->addSelect('p.age')
            ->addSelect($this->getStatisticsSubQuery(1, $filters) . ' as minutes')
            ->addSelect($this->getStatisticsSubQuery(2, $filters) . ' as goals')
            ->addSelect('p.link')
            ->leftJoin('App:PlayerStatistics', 's', 'WITH', 's.player = p.id')
            ->addGroupBy('p.id');
    }

    private function resolvePagination(QueryBuilder $qb, array $filters): void
    {
        if (empty($filters['limit'])) {
            $qb->setMaxResults(25);
        } else {
            $qb->setMaxResults($filters['limit']);
        }
        if (false === empty($filters['page'])) {
            $firstResult = ($filters['page'] - 1) * ($filters['limit'] ?? 25);
            $qb->setFirstResult((int) $firstResult);
        }
    }

    private function resolveName(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['playerName'])) {
            $qb->andWhere('LOWER('. self::NAME . ') LIKE :name')
                ->setParameter('name', '%' . \strtolower($filters['playerName']) . '%', Types::STRING);
        }
    }

    private function resolveAge(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['ageFrom'])) {
            $qb->andWhere('p.age >= :ageFrom')
                ->setParameter(':ageFrom', $filters['ageFrom'], Types::INTEGER);
        }

        if (isset($filters['ageTo']) && \is_int($filters['ageTo'])) {
            $qb->andWhere('p.age <= :ageTo')
                ->setParameter(':ageTo', $filters['ageTo'], Types::INTEGER);
        }
    }

    private function resolveMinutes(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['minutesFrom'])) {
            $qb->andWhere($this->getStatisticsSubQuery(1, $filters) . ' >= :minutesFrom')
                ->setParameter(':minutesFrom', $filters['minutesFrom'], Types::INTEGER);
        }

        if (isset($filters['minutesTo']) && \is_int($filters['minutesTo'])) {
            $qb->andWhere($this->getStatisticsSubQuery(1, $filters) . ' <= :minutesTo')
                ->setParameter(':minutesTo', $filters['minutesTo'], Types::INTEGER);
        }
    }

    private function resolveGoals(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['goalsFrom'])) {
            $qb->andWhere($this->getStatisticsSubQuery(2, $filters) . ' >= :goalsFrom')
                ->setParameter(':goalsFrom', $filters['goalsFrom'], Types::INTEGER);
        }
        if (isset($filters['goalsTo']) && \is_int($filters['goalsTo'])) {
            $qb->andWhere($this->getStatisticsSubQuery(2, $filters) . ' <= :goalsTo')
                ->setParameter(':goalsTo', $filters['goalsTo'], Types::INTEGER);
        }
    }

    private function resolveLeague(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['league'])) {
            $qb
                ->addSelect('l.name as league')
                ->innerJoin('p.teams', 't')
                ->innerJoin('t.league', 'l')
                ->andWhere('l IN (:league)')
                ->setParameter(':league', $filters['league'])
                ->addGroupBy('l')
            ;
        }
    }

    private function resolveSeason(QueryBuilder $qb, array $filters, string $statsAlias = 's'): void
    {
        if (empty($filters['season']) || $filters['season'] === 'any') {
            return;
        }

        $qb->addSelect($statsAlias . '.season');

        if ($filters['season'] !== 'group') {
            $qb
                ->andWhere($statsAlias . '.season = :season')
                ->setParameter('season', $filters['season'], Types::STRING);

            return;
        }

        if ($filters['season'] === 'group') {
            $qb->addGroupBy($statsAlias . '.season');
        }
    }

    private function resolveDate(QueryBuilder $qb, array $filters, string $statsAlis = 's'): void
    {
        if (false === empty($filters['dateFrom'])) {
            $qb->andWhere($statsAlis . '.date >= :dateFrom')
                ->setParameter(':dateFrom', $filters['dateFrom']);
        }

        if (false === empty($filters['dateTo'])) {
            $qb->andWhere($statsAlis . '.date <= :dateTo')
                ->setParameter(':dateTo', $filters['dateTo']);
        }
    }

    private function resolveOrderBy(QueryBuilder $qb, array $filters): void
    {
        $sort = [];
        $order = 0;

        if (false === empty($filters['orderByPlayer']['way'])) {
            $sort[$filters['orderByPlayer']['order'] ?? $order++] = ['name' => 'p.firstName', 'way' => $filters['orderByPlayer']['way']];
            $sort[$filters['orderByPlayer']['order'] ?? $order++] = ['name' => 'p.lastName', 'way' => $filters['orderByPlayer']['way']];
        }

        if (false === empty($filters['orderByAge']['way'])) {
            $sort[$filters['orderByAge']['order'] ?? $order++] = ['name' => 'p.age', 'way' => $filters['orderByAge']['way']];
        }

        if (false === empty($filters['orderByGoals']['way'])) {
            $sort[$filters['orderByGoals']['order'] ?? $order++] = ['name' => 'goals', 'way' => $filters['orderByGoals']['way']];
        }

        if (false === empty($filters['orderByMinutes']['way'])) {
            $sort[$filters['orderByMinutes']['order'] ?? $order++] = ['name' => 'minutes', 'way' => $filters['orderByMinutes']['way']];
        }

        \krsort($sort);
        foreach ($sort as $sortItem) {
            $qb->addOrderBy($sortItem['name'], $sortItem['way']);
        }
    }

    private function getStatisticsSubQuery(int $type, array $filters): string
    {
        $alias = 's' . $this->subQueryCount++;

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COALESCE(SUM(' . $alias . '.value), 0)')
            ->from('App:PlayerStatistics', $alias)
            ->andWhere($alias . '.type = ' . $type)
            ->andWhere($alias . '.player = p.id')
        ;

        $this->resolveLeagueForSubQuery($qb, $filters, $alias);
        $this->resolveSeasonForSubQuery($qb, $filters, $alias);
        $this->resolveDate($qb, $filters, $alias);

        return '(' . $qb->getQuery()->getDQL() . ')';
    }

    private function resolveLeagueForSubQuery(QueryBuilder $qb, array $filters, string $alias): void
    {
        if (false === empty($filters['league'])) {
            $qb
                ->innerJoin($alias . '.player', 'p' . $this->subQueryCount)
                ->innerJoin('p' . $this->subQueryCount . '.teams', 't' . $this->subQueryCount)
                ->innerJoin('t'. $this->subQueryCount . '.league', 'l' . $this->subQueryCount)
                ->andWhere('l' . $this->subQueryCount .  '.id = l.id')
            ;
        }
    }

    private function resolveSeasonForSubQuery(QueryBuilder $qb, array $filters, string $statsAlias): void
    {
        if (empty($filters['season']) || $filters['season'] === 'any') {
            return;
        }

        if ($filters['season'] !== 'group') {
            $qb
                ->andWhere($statsAlias . '.season = :season')
                ->setParameter('season', $filters['season'], Types::STRING);

            return;
        }

        if ($filters['season'] === 'group') {
            $qb->andWhere($statsAlias . '.season = s.season');
        }
    }
}