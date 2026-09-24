<?php

declare(strict_types=1);

namespace App\Ticket\Application\MoveTicketToSprint;

final class MoveTicketToSprintPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $requesterMemberId,
        public readonly ?string $sprintId,
    ) {
    }
}
