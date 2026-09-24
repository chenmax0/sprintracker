<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Application\Port\ColumnLookupInterface;
use Doctrine\DBAL\Connection;

final class SqlColumnLookup implements ColumnLookupInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function belongsToProject(string $columnId, string $projectId): bool
    {
        $found = $this->connection->fetchOne(
            'SELECT 1 FROM board_column WHERE id = :column_id AND project_id = :project_id',
            ['column_id' => $columnId, 'project_id' => $projectId],
        );

        return false !== $found;
    }

    public function firstColumnId(string $projectId): string
    {
        return (string) $this->connection->fetchOne(
            'SELECT id FROM board_column WHERE project_id = :project_id ORDER BY position ASC LIMIT 1',
            ['project_id' => $projectId],
        );
    }
}
