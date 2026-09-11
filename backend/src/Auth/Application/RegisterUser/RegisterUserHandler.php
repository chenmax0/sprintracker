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
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher,
        private UserIdGeneratorInterface $ids,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): User
    {
        $email = new Email($command->email);

        if (null !== $this->users->findByEmail($email)) {
            throw new EmailAlreadyUsedException($command->email);
        }

        $user = User::register($this->ids->generate(), $email, $command->name, $this->hasher->hash($command->plainPassword));
        $this->users->save($user);

        return $user;
    }
}
