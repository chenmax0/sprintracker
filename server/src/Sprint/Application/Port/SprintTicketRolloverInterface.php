<?php

declare(strict_types=1);

namespace App\Sprint\Application\Port;

/**
 * Anti-corruption layer towards the Ticket context: when a sprint completes,
 * its unfinished tickets move to the next sprint and get a history entry
 * recording they passed through the completed one. Implemented in
 * Infrastructure with direct SQL on the ticket/ticket_sprint_history tables
 * instead of depending on Ticket's Domain classes.
 */
interface SprintTicketRolloverInterface
{
    public function rolloverIncompleteTickets(string $completedSprintId, string $nextSprintId): void;
}
