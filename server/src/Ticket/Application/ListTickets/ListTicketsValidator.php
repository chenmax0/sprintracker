<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTickets;

use Assert\Assert;

final class ListTicketsValidator
{
    public function validate(ListTicketsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
