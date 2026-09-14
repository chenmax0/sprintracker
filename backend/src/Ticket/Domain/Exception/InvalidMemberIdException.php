<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class InvalidMemberIdException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('"%s" is not a valid member id.', $value));
    }
}
