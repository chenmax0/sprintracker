<?php

declare(strict_types=1);

namespace App\Ticket\Application\CreateTicket;

final class CreateTicketPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $sprintId,
        public readonly ?string $assigneeId,
    ) {
    }
}
