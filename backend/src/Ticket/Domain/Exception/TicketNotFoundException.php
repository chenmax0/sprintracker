<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class TicketNotFoundException extends \DomainException
{
    public function __construct(string $ticketId)
    {
        parent::__construct(sprintf('Ticket "%s" not found.', $ticketId));
    }
}
