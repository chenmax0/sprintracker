<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence\Sql;

use App\Analytics\Application\Port\VisitorActivityRecorderInterface;
use Doctrine\DBAL\Connection;

final class SqlVisitorActivityRecorder implements VisitorActivityRecorderInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function record(string $visitorId, ?string $userId, \DateTimeImmutable $hour): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO visitor_activity (visitor_id, bucket_hour, user_id)
                VALUES (:visitor_id, :bucket_hour, :user_id)
                ON CONFLICT (visitor_id, bucket_hour) DO UPDATE SET
                    user_id = COALESCE(EXCLUDED.user_id, visitor_activity.user_id)
                SQL,
            [
                'visitor_id' => $visitorId,
                'bucket_hour' => $hour->format('Y-m-d H:i:s'),
                'user_id' => $userId,
            ],
        );
    }
}
