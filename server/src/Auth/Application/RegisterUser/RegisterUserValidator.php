<?php

declare(strict_types=1);

namespace App\Auth\Application\RegisterUser;

use Assert\Assert;

final class RegisterUserValidator
{
    public function validate(RegisterUserPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->email, 'email')->notBlank()->email()
            ->that($payload->name, 'name')->notBlank()
            ->that($payload->plainPassword, 'plainPassword')->notBlank()->minLength(8)
            ->verifyNow();
    }
}
