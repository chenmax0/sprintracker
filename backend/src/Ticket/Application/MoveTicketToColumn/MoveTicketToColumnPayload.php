<?php

declare(strict_types=1);

namespace App\Ticket\Application\MoveTicketToColumn;

final class MoveTicketToColumnPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $requesterMemberId,
        public readonly string $columnId,
    ) {
    }
}
