<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

use App\Ticket\Application\ListTicketSprintHistory\TicketSprintHistoryEntry;

interface TicketSprintHistoryRepositoryInterface
{
    /**
     * @return list<TicketSprintHistoryEntry>
     */
    public function findByTicketId(string $ticketId): array;

    /**
     * @param list<string> $ticketIds
     *
     * @return array<string, int> ticketId => number of sprints it was carried over from
     */
    public function countByTicketIds(array $ticketIds): array;
}
