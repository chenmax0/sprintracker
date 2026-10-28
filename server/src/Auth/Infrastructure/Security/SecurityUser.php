<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    private function __construct(
        private string $id,
        private string $email,
        private string $name,
        private string $hashedPassword,
        private array $roles,
    ) {
    }

    public static function fromDomain(User $user): self
    {
        return new self(
            (string) $user->getId(),
            (string) $user->getEmail(),
            $user->getName(),
            $user->getHashedPassword(),
            $user->getRoles(),
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        return array_unique([...$this->roles, 'ROLE_USER']);
    }

    public function getPassword(): string
    {
        return $this->hashedPassword;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }
}
