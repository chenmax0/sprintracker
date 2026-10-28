<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Application\Port\SprintLookupInterface;
use Doctrine\DBAL\Connection;

final class SqlSprintLookup implements SprintLookupInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function belongsToProject(string $sprintId, string $projectId): bool
    {
        $found = $this->connection->fetchOne(
            'SELECT 1 FROM sprint WHERE id = :sprint_id AND project_id = :project_id',
            ['sprint_id' => $sprintId, 'project_id' => $projectId],
        );

        return false !== $found;
    }
}
