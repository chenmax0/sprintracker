<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListComments;

use Assert\Assert;

final class ListCommentsValidator
{
    public function validate(ListCommentsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
