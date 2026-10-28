<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTickets;

final class ListTicketsPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
    ) {
    }
}
