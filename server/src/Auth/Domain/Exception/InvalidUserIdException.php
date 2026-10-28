<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidUserIdException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('"%s" is not a valid user id.', $value));
    }
}
