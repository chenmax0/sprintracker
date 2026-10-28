<?php

declare(strict_types=1);

namespace App\Ticket\Application\MoveTicketToColumn;

use Assert\Assert;

final class MoveTicketToColumnValidator
{
    public function validate(MoveTicketToColumnPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->columnId, 'columnId')->notBlank()
            ->verifyNow();
    }
}
