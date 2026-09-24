<?php

declare(strict_types=1);

namespace App\Ticket\Application\MoveTicketToSprint;

use Assert\Assert;

final class MoveTicketToSprintValidator
{
    public function validate(MoveTicketToSprintPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
