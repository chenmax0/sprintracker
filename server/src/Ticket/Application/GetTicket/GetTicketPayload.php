<?php

declare(strict_types=1);

namespace App\Ticket\Application\GetTicket;

final class GetTicketPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $requesterMemberId,
    ) {
    }
}
