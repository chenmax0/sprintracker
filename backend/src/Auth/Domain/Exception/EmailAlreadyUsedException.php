<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class EmailAlreadyUsedException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Email "%s" is already used.', $email));
    }
}
