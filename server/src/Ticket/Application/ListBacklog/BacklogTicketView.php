<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListBacklog;

final class BacklogTicketView
{
    public function __construct(
        public readonly string $id,
        public readonly int $number,
        public readonly string $title,
        public readonly ?string $assigneeId,
        public readonly string $createdAt,
        public readonly int $carriedOverCount,
    ) {
    }
}
