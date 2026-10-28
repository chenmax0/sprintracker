<?php

declare(strict_types=1);

namespace App\Ticket\Application\AddComment;

use Assert\Assert;

final class AddCommentValidator
{
    public function validate(AddCommentPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->authorMemberId, 'authorMemberId')->notBlank()
            ->that($payload->content, 'content')->notBlank()
            ->verifyNow();
    }
}
