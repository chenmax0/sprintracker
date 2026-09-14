<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class InvalidTicketIdException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('"%s" is not a valid ticket id.', $value));
    }
}
