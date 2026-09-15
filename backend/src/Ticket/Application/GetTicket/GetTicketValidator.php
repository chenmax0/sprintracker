<?php

declare(strict_types=1);

namespace App\Ticket\Application\GetTicket;

use Assert\Assert;

final class GetTicketValidator
{
    public function validate(GetTicketPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
