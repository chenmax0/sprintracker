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

    public function findByTeamId(TeamId $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, team_id, name FROM project WHERE team_id = :team_id',
            ['team_id' => (string) $teamId],
        );

        return array_map(
            static fn (array $row) => Project::fromPersistence(new ProjectId($row['id']), new TeamId($row['team_id']), $row['name']),
            $rows,
        );
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

    public function delete(ProjectId $id): void
    {
        $projectId = (string) $id;

        // No ON DELETE CASCADE on these FKs, so children are removed by hand,
        // in dependency order (history/comments before the tickets/sprints
        // they reference, those before the project itself).
        $this->connection->executeStatement(
            'DELETE FROM ticket_sprint_history WHERE ticket_id IN (SELECT id FROM ticket WHERE project_id = :project_id)',
            ['project_id' => $projectId],
        );
        $this->connection->executeStatement(
            'DELETE FROM comment WHERE ticket_id IN (SELECT id FROM ticket WHERE project_id = :project_id)',
            ['project_id' => $projectId],
        );
        $this->connection->executeStatement('DELETE FROM ticket WHERE project_id = :project_id', ['project_id' => $projectId]);
        $this->connection->executeStatement('DELETE FROM board_column WHERE project_id = :project_id', ['project_id' => $projectId]);
        $this->connection->executeStatement('DELETE FROM sprint WHERE project_id = :project_id', ['project_id' => $projectId]);
        $this->connection->executeStatement('DELETE FROM project WHERE id = :id', ['id' => $projectId]);
    }
}
