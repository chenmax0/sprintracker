<?php

declare(strict_types=1);

namespace App\Auth\Domain;

interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?User;

    public function save(User $user): void;
}
