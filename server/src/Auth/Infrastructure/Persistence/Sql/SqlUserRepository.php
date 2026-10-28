<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Sql;

use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserId;
use App\Auth\Domain\UserRepositoryInterface;
use Doctrine\DBAL\Connection;

final class SqlUserRepository implements UserRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findByEmail(Email $email): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, email, name, password, roles FROM "user" WHERE email = :email',
            ['email' => (string) $email],
        );

        if (false === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function save(User $user): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO "user" (id, email, name, password, roles)
                VALUES (:id, :email, :name, :password, :roles)
                ON CONFLICT (id) DO UPDATE SET
                    email = EXCLUDED.email,
                    name = EXCLUDED.name,
                    password = EXCLUDED.password,
                    roles = EXCLUDED.roles
                SQL,
            [
                'id' => (string) $user->getId(),
                'email' => (string) $user->getEmail(),
                'name' => $user->getName(),
                'password' => $user->getHashedPassword(),
                'roles' => json_encode($user->getRoles(), JSON_THROW_ON_ERROR),
            ],
        );
    }

    private function hydrate(array $row): User
    {
        return User::fromPersistence(
            new UserId($row['id']),
            new Email($row['email']),
            $row['name'],
            $row['password'],
            json_decode($row['roles'], true, flags: JSON_THROW_ON_ERROR),
        );
    }
}
