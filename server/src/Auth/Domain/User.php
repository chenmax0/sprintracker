<?php

declare(strict_types=1);

namespace App\Auth\Domain;

class User
{
    /**
     * @param list<string> $roles
     */
    private function __construct(
        private UserId $id,
        private Email $email,
        private string $name,
        private string $hashedPassword,
        private array $roles = ['ROLE_USER'],
    ) {
    }

    public static function register(UserId $id, Email $email, string $name, string $hashedPassword): self
    {
        return new self($id, $email, $name, $hashedPassword);
    }

    /**
     * @param list<string> $roles
     */
    public static function fromPersistence(UserId $id, Email $email, string $name, string $hashedPassword, array $roles): self
    {
        return new self($id, $email, $name, $hashedPassword, $roles);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
