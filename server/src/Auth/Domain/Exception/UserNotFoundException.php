<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class UserNotFoundException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('No user with email "%s".', $email));
    }
}
