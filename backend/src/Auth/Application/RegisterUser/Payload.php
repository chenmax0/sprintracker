<?php

declare(strict_types=1);

namespace App\Auth\Application\RegisterUser;

final class Payload
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $plainPassword,
    ) {
    }
}
