<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTicketSprintHistory;

use Assert\Assert;

final class ListTicketSprintHistoryValidator
{
    public function validate(ListTicketSprintHistoryPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
