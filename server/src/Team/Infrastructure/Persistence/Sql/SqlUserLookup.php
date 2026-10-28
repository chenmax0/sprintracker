<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Persistence\Sql;

use App\Team\Application\Port\UserLookupInterface;
use Doctrine\DBAL\Connection;

final class SqlUserLookup implements UserLookupInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findIdByEmail(string $email): ?string
    {
        $id = $this->connection->fetchOne('SELECT id FROM "user" WHERE email = :email', ['email' => $email]);

        return false === $id ? null : $id;
    }
}
