<?php

declare(strict_types=1);

namespace App\Ticket\Application\AddComment;

final class AddCommentPayload
{
    public function __construct(
        public readonly string $ticketId,
        public readonly string $authorMemberId,
        public readonly string $content,
    ) {
    }
}
