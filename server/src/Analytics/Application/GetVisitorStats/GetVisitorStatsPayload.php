<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetVisitorStats;

final class GetVisitorStatsPayload
{
    /**
     * @param ?string $from first local day to include (Y-m-d); defaults to 29 days before $to
     * @param ?string $to   last local day to include (Y-m-d); defaults to today
     */
    public function __construct(
        public readonly string $granularity,
        public readonly string $timezone,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
    ) {
    }
}
