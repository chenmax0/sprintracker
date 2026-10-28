<?php

declare(strict_types=1);

namespace App\Auth\Application\PromoteUserToAdmin;

use App\Auth\Domain\Email;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\UserRepositoryInterface;

final class PromoteUserToAdminHandler
{
    public function __construct(
        private PromoteUserToAdminValidator $validator,
        private UserRepositoryInterface $users,
    ) {
    }

    public function handle(PromoteUserToAdminPayload $payload): void
    {
        $this->validator->validate($payload);

        $user = $this->users->findByEmail(new Email($payload->email));

        if (null === $user) {
            throw new UserNotFoundException($payload->email);
        }

        $user->grantAdmin();
        $this->users->save($user);
    }
}
