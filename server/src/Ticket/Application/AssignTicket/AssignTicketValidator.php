<?php

declare(strict_types=1);

namespace App\Ticket\Application\AssignTicket;

use Assert\Assert;

final class AssignTicketValidator
{
    public function validate(AssignTicketPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
