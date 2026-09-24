<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

/**
 * Anti-corruption layer towards the Board context: Ticket never depends on
 * Board's Domain classes, it only asks "does this column belong to this
 * project?" through this port, implemented in Infrastructure with a direct
 * SQL query on the board_column table.
 */
interface ColumnLookupInterface
{
    public function belongsToProject(string $columnId, string $projectId): bool;

    /**
     * The leftmost column (lowest position) of a project's board - where
     * new tickets land by default.
     */
    public function firstColumnId(string $projectId): string;
}
