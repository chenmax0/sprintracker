<?php

declare(strict_types=1);

namespace App\Ticket\Application\CreateTicket;

use Assert\Assert;

final class CreateTicketValidator
{
    public function validate(CreateTicketPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->title, 'title')->notBlank()
            ->verifyNow();
    }
}
