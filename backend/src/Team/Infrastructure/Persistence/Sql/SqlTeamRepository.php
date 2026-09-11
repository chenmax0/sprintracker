<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Persistence\Sql;

use App\Team\Domain\Team;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamRepositoryInterface;
use Doctrine\DBAL\Connection;

final class SqlTeamRepository implements TeamRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(TeamId $id): ?Team
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, name FROM team WHERE id = :id',
            ['id' => (string) $id],
        );

        if (false === $row) {
            return null;
        }

        return Team::fromPersistence(new TeamId($row['id']), $row['name']);
    }

    public function save(Team $team): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO team (id, name)
                VALUES (:id, :name)
                ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name
                SQL,
            [
                'id' => (string) $team->getId(),
                'name' => $team->getName(),
            ],
        );
    }
}
