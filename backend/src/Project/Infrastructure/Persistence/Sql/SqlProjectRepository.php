<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Persistence\Sql;

use App\Project\Domain\Project;
use App\Project\Domain\ProjectId;
use App\Project\Domain\ProjectRepositoryInterface;
use App\Project\Domain\TeamId;
use Doctrine\DBAL\Connection;

final class SqlProjectRepository implements ProjectRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(ProjectId $id): ?Project
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, team_id, name FROM project WHERE id = :id',
            ['id' => (string) $id],
        );

        if (false === $row) {
            return null;
        }

        return Project::fromPersistence(new ProjectId($row['id']), new TeamId($row['team_id']), $row['name']);
    }

    public function save(Project $project): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO project (id, team_id, name)
                VALUES (:id, :team_id, :name)
                ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name
                SQL,
            [
                'id' => (string) $project->getId(),
                'team_id' => (string) $project->getTeamId(),
                'name' => $project->getName(),
            ],
        );
    }
}
