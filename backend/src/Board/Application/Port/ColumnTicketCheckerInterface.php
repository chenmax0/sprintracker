<?php

declare(strict_types=1);

namespace App\Board\Application\Port;

/**
 * Anti-corruption layer towards the Ticket context: Board never depends on
 * Ticket's Domain classes, it only asks "does this column still have
 * tickets in it?" through this port, implemented in Infrastructure with a
 * direct SQL query on the ticket table.
 */
interface ColumnTicketCheckerInterface
{
    public function hasTickets(string $columnId): bool;
}
