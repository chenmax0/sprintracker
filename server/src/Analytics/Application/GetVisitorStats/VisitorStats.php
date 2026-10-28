<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetVisitorStats;

final class VisitorStats
{
    /**
     * @param list<VisitorStatsBucket> $series
     * @param int                      $totalVisitors distinct visitors over the whole window (not the sum of the buckets)
     * @param int                      $totalUsers    distinct signed-in users over the whole window
     */
    public function __construct(
        public readonly array $series,
        public readonly int $totalVisitors,
        public readonly int $totalUsers,
    ) {
    }
}
