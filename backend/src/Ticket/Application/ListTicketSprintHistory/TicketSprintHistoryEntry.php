<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTicketSprintHistory;

final class TicketSprintHistoryEntry
{
    public function __construct(
        public readonly string $sprintId,
        public readonly \DateTimeImmutable $recordedAt,
    ) {
    }
}
