<?php

declare(strict_types=1);

namespace App\Ticket\Application\EditComment;

use Assert\Assert;

final class EditCommentValidator
{
    public function validate(EditCommentPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->commentId, 'commentId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->content, 'content')->notBlank()
            ->verifyNow();
    }
}
