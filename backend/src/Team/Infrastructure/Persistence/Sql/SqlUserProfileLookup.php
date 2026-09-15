<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Persistence\Sql;

use App\Team\Application\Port\UserProfileLookupInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final class SqlUserProfileLookup implements UserProfileLookupInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findByIds(array $userIds): array
    {
        if ([] === $userIds) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, email, name FROM "user" WHERE id IN (:ids)',
            ['ids' => $userIds],
            ['ids' => ArrayParameterType::STRING],
        );

        $profiles = [];
        foreach ($rows as $row) {
            $profiles[$row['id']] = ['email' => $row['email'], 'name' => $row['name']];
        }

        return $profiles;
    }
}
