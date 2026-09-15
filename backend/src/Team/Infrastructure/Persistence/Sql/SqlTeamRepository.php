<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Persistence\Sql;

use App\Team\Domain\MemberId;
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

    public function findByMemberId(MemberId $memberId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<'SQL'
                SELECT t.id, t.name
                FROM team t
                INNER JOIN team_membership tm ON tm.team_id = t.id
                WHERE tm.user_id = :member_id
                SQL,
            ['member_id' => (string) $memberId],
        );

        return array_map(
            static fn (array $row) => Team::fromPersistence(new TeamId($row['id']), $row['name']),
            $rows,
        );
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
