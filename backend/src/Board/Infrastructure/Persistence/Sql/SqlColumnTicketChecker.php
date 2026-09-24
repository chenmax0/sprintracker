<?php

declare(strict_types=1);

namespace App\Board\Infrastructure\Persistence\Sql;

use App\Board\Application\Port\ColumnTicketCheckerInterface;
use Doctrine\DBAL\Connection;

final class SqlColumnTicketChecker implements ColumnTicketCheckerInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function hasTickets(string $columnId): bool
    {
        $found = $this->connection->fetchOne(
            'SELECT 1 FROM ticket WHERE column_id = :column_id',
            ['column_id' => $columnId],
        );

        return false !== $found;
    }
}
