<?php

declare(strict_types=1);

namespace App\Ticket\Application\EditComment;

final class EditCommentPayload
{
    public function __construct(
        public readonly string $commentId,
        public readonly string $requesterMemberId,
        public readonly string $content,
    ) {
    }
}
