<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

/**
 * Anti-corruption layer towards the Sprint context: Ticket never depends on
 * Sprint's Domain classes, it only asks "does this sprint belong to this
 * project?" through this port, implemented in Infrastructure with a direct
 * SQL query on the sprint table.
 */
interface SprintLookupInterface
{
    public function belongsToProject(string $sprintId, string $projectId): bool;
}
