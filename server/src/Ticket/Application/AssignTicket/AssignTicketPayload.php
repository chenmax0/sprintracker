<?php

declare(strict_types=1);

namespace App\Ticket\Application\AssignTicket;

final class AssignTicketPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $requesterMemberId,
        public readonly ?string $assigneeId,
    ) {
    }
}
