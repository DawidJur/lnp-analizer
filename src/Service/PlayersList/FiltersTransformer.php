<?php

namespace App\Service\PlayersList;

class FiltersTransformer
{
    public function transform(array $filters): array
    {
        $filters = $this->transformLeague($filters);

        return $filters;
    }

    private function transformLeague(array $filters): array
    {
        if (empty($filters['league'])) {
            return $filters;
        }

        $leagues = [];
        foreach ($filters['league'] as $league) {
            $leagues[] = $league->getId();
        }

        $filters['league'] = $leagues;

        return $filters;
    }
}