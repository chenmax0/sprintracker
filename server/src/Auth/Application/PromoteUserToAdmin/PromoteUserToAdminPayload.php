<?php

declare(strict_types=1);

namespace App\Auth\Application\PromoteUserToAdmin;

final class PromoteUserToAdminPayload
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}
