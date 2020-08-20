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

    public function __construct(EntityManagerInterface $entityManager)
    {
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
            ->from('App:PlayerStatistics', 's')
            ->addSelect('p.id')
            ->addSelect(self::NAME . ' as name')
            ->addSelect('p.age')
            ->addSelect($this->getStatisticsSubQuery(1, $filters) . ' as minutes')
            ->addSelect($this->getStatisticsSubQuery(2, $filters) . ' as goals')
            ->addSelect('p.link')
            ->innerJoin('s.player', 'p', 'WITH', 's.player = p.id')
            ->addGroupBy('name');
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
            $qb->setFirstResult((int)$firstResult);
        }
    }

    private function resolveName(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['playerName'])) {
            $qb->andWhere(self::NAME . ' LIKE :name')
                ->setParameter('name', '%' . $filters['playerName'] . '%', Types::STRING);
        }
    }

    private function resolveAge(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['ageFrom'])) {
            $qb->andWhere('p.age >= :ageFrom')
                ->setParameter(':ageFrom', $filters['ageFrom'], Types::INTEGER);
        }

        if (false === empty($filters['ageTo'])) {
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

        if (false === empty($filters['minutesTo'])) {
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
        if (false === empty($filters['goalsTo'])) {
            $qb->andWhere($this->getStatisticsSubQuery(2, $filters) . ' <= :goalsTo')
                ->setParameter(':goalsTo', $filters['goalsTo'], Types::INTEGER);
        }
    }

    private function resolveLeague(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['league'])) {
            $qb->innerJoin('p.teams', 't')
                ->innerJoin('t.league', 'l')
                ->andWhere('l.name = :league')
                ->setParameter(':league', $filters['league'], Types::STRING);
        }
    }

    private function resolveSeason(QueryBuilder $qb, array $filters, string $statsAlias = 's'): void
    {
        if (empty($filters['season']) || $filters['season'] === 'any') {
            return;
        }

        if ($statsAlias === 's') {
            $qb->addSelect($statsAlias . '.season');
        }

        if ($filters['season'] !== 'group') {
            $qb
                ->andWhere($statsAlias . '.season = :season')
                ->setParameter('season', $filters['season'], Types::STRING);

            return;
        }

        if ($filters['season'] === 'group' && $statsAlias !== 's') {dump($statsAlias);
            $qb->andWhere($statsAlias . '.season = s.season');

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
            $sort[$filters['orderByPlayer']['order'] ?? $order++] = ['name' => self::NAME, 'way' => $filters['orderByPlayer']['way']];
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
            ->select('SUM(' . $alias . '.value)')
            ->from('App:PlayerStatistics', $alias)
            ->andWhere($alias . '.type = ' . $type)
            ->andWhere($alias . '.player = s.player')
        ;

        $this->resolveLeagueForSubQuery($qb, $filters);
        $this->resolveSeason($qb, $filters, $alias);
        $this->resolveDate($qb, $filters, $alias);

        dump($qb->getQuery()->getDQL());

        return '(' . $qb->getQuery()->getDQL() . ')';
    }

    private function resolveLeagueForSubQuery(QueryBuilder $qb, array $filters): void
    {
        if (false === empty($filters['league'])) {
            $qb
                ->innerJoin('p' . $this->subQueryCount. '.teams', 't')
                ->innerJoin('t'. $this->subQueryCount . '.league', 'l')
                ->andWhere('l' . $this->subQueryCount.  '.name = :league')
                ->setParameter(':league', $filters['league'], Types::STRING);
        }
    }
}