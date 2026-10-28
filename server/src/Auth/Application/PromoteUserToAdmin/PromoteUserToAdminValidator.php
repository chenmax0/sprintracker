<?php

declare(strict_types=1);

namespace App\Auth\Application\PromoteUserToAdmin;

use Assert\Assert;

final class PromoteUserToAdminValidator
{
    public function validate(PromoteUserToAdminPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->email, 'email')->notBlank()->email()
            ->verifyNow();
    }
}
