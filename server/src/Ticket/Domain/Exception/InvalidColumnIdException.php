<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class InvalidColumnIdException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('"%s" is not a valid column id.', $value));
    }
}
