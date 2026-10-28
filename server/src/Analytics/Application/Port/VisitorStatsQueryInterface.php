<?php

declare(strict_types=1);

namespace App\Analytics\Application\Port;

use App\Analytics\Application\GetVisitorStats\VisitorStats;
use App\Analytics\Domain\Granularity;

interface VisitorStatsQueryInterface
{
    /**
     * Counts distinct visitors per bucket for the local days $from..$to
     * (inclusive), in $timezone. The first bucket is widened to its natural
     * start (e.g. the Monday of the week) so no bucket is ever partial at the
     * start, and empty buckets are returned with zero counts.
     */
    public function query(Granularity $granularity, \DateTimeImmutable $from, \DateTimeImmutable $to, string $timezone): VisitorStats;
}
