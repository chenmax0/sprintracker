<?php

declare(strict_types=1);

namespace App\Ticket\Application\UpdateTicketStatus;

use App\Ticket\Domain\TicketStatus;
use Assert\Assert;

final class UpdateTicketStatusValidator
{
    public function validate(UpdateTicketStatusPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->ticketId, 'ticketId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->status, 'status')->notBlank()->choice(array_map(
                static fn (TicketStatus $status) => $status->value,
                TicketStatus::cases(),
            ))
            ->verifyNow();
    }
}
