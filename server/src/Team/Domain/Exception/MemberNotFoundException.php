<?php

declare(strict_types=1);

namespace App\Team\Domain\Exception;

final class MemberNotFoundException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('No user found with email "%s".', $email));
    }
}
