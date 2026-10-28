<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetVisitorStats;

use App\Analytics\Application\Port\VisitorStatsQueryInterface;
use App\Analytics\Domain\Granularity;

final class GetVisitorStatsHandler
{
    public function __construct(
        private GetVisitorStatsValidator $validator,
        private VisitorStatsQueryInterface $stats,
    ) {
    }

    public function handle(GetVisitorStatsPayload $payload): VisitorStats
    {
        $this->validator->validate($payload);

        $timezone = new \DateTimeZone($payload->timezone);
        $to = new \DateTimeImmutable($payload->to ?? 'today', $timezone);
        $from = null !== $payload->from
            ? new \DateTimeImmutable($payload->from, $timezone)
            : $to->modify('-29 days');

        return $this->stats->query(Granularity::from($payload->granularity), $from, $to, $payload->timezone);
    }
}
