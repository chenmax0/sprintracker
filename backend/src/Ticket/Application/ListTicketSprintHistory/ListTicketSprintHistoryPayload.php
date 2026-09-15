<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTicketSprintHistory;

final class ListTicketSprintHistoryPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $requesterMemberId,
    ) {
    }
}
