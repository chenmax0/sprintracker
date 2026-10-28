<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListComments;

final class ListCommentsPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $requesterMemberId,
    ) {
    }
}
