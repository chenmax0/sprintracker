<?php

declare(strict_types=1);

namespace App\Auth\Application\RegisterUser;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\Exception\EmailAlreadyUsedException;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;

final class RegisterUserHandler
{
    public function __construct(
        private RegisterUserValidator $validator,
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher,
        private UserIdGeneratorInterface $ids,
    ) {
    }

    public function handle(RegisterUserPayload $payload): User
    {
        $this->validator->validate($payload);

        $email = new Email($payload->email);

        if (null !== $this->users->findByEmail($email)) {
            throw new EmailAlreadyUsedException($payload->email);
        }

        $user = User::register($this->ids->generate(), $email, $payload->name, $this->hasher->hash($payload->plainPassword));
        $this->users->save($user);

        return $user;
    }
}
