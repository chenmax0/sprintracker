<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence\Sql;

use App\Analytics\Application\GetVisitorStats\VisitorStats;
use App\Analytics\Application\GetVisitorStats\VisitorStatsBucket;
use App\Analytics\Application\Port\VisitorStatsQueryInterface;
use App\Analytics\Domain\Granularity;
use Doctrine\DBAL\Connection;

final class SqlVisitorStatsQuery implements VisitorStatsQueryInterface
{
    /**
     * Local bounds: lo_local is the start of the first bucket, hi_local the
     * (exclusive) end of the last requested day. bucket_hour is stored in UTC,
     * so it is converted to the admin's timezone before being truncated, and
     * the bounds are converted back to UTC to stay index-friendly.
     */
    private const BOUNDS = <<<'SQL'
        b AS (
            SELECT date_trunc(:granularity, CAST(:from_local AS timestamp)) AS lo_local,
                   CAST(:to_local AS timestamp) AS hi_local
        ),
        window_rows AS (
            SELECT a.visitor_id, a.user_id, a.bucket_hour
            FROM visitor_activity a, b
            WHERE a.bucket_hour >= ((b.lo_local AT TIME ZONE :timezone) AT TIME ZONE 'UTC')
              AND a.bucket_hour <  ((b.hi_local AT TIME ZONE :timezone) AT TIME ZONE 'UTC')
        )
        SQL;

    public function __construct(private Connection $connection)
    {
    }

    public function query(Granularity $granularity, \DateTimeImmutable $from, \DateTimeImmutable $to, string $timezone): VisitorStats
    {
        $params = [
            'granularity' => $granularity->value,
            'from_local' => $from->format('Y-m-d').' 00:00:00',
            'to_local' => $to->modify('+1 day')->format('Y-m-d').' 00:00:00',
            'timezone' => $timezone,
        ];

        $rows = $this->connection->fetchAllAssociative(
            'WITH '.self::BOUNDS.',
                series AS (
                    SELECT generate_series(b.lo_local, b.hi_local - INTERVAL \'1 second\', CAST(\'1 \' || :granularity AS interval)) AS bucket
                    FROM b
                ),
                counts AS (
                    SELECT date_trunc(:granularity, (w.bucket_hour AT TIME ZONE \'UTC\') AT TIME ZONE :timezone) AS bucket,
                           COUNT(DISTINCT w.visitor_id) AS visitors,
                           COUNT(DISTINCT w.user_id) AS users
                    FROM window_rows w
                    GROUP BY 1
                )
            SELECT to_char(s.bucket, \'YYYY-MM-DD"T"HH24:MI:SS\') AS bucket,
                   COALESCE(c.visitors, 0) AS visitors,
                   COALESCE(c.users, 0) AS users
            FROM series s
            LEFT JOIN counts c ON c.bucket = s.bucket
            ORDER BY s.bucket',
            $params,
        );

        $totals = $this->connection->fetchAssociative(
            'WITH '.self::BOUNDS.'
            SELECT COUNT(DISTINCT visitor_id) AS visitors, COUNT(DISTINCT user_id) AS users FROM window_rows',
            $params,
        );

        return new VisitorStats(
            array_map(
                static fn (array $row) => new VisitorStatsBucket($row['bucket'], (int) $row['visitors'], (int) $row['users']),
                $rows,
            ),
            (int) $totals['visitors'],
            (int) $totals['users'],
        );
    }
}
