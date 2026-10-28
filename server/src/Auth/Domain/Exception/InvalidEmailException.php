<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidEmailException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('"%s" is not a valid email address.', $value));
    }
}
