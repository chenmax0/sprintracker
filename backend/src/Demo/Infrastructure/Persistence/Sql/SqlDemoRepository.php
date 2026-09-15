<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Persistence\Sql;

use App\Demo\Application\Port\DemoRepositoryInterface;
use Doctrine\DBAL\Connection;

final class SqlDemoRepository implements DemoRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private string $demoTeamId,
    ) {
    }

    public function getSnapshot(): array
    {
        $team = $this->connection->fetchAssociative(
            'SELECT id, name FROM team WHERE id = :team_id',
            ['team_id' => $this->demoTeamId],
        );

        $project = $this->connection->fetchAssociative(
            'SELECT id, team_id, name FROM project WHERE team_id = :team_id LIMIT 1',
            ['team_id' => $this->demoTeamId],
        );

        $members = $this->connection->fetchAllAssociative(
            'SELECT tm.user_id AS member_id, tm.role, u.email, u.name
             FROM team_membership tm
             INNER JOIN "user" u ON u.id = tm.user_id
             WHERE tm.team_id = :team_id',
            ['team_id' => $this->demoTeamId],
        );

        $sprints = [];
        $tickets = [];

        if (false !== $project) {
            $sprints = $this->connection->fetchAllAssociative(
                'SELECT id, project_id, number, start_date, end_date, status FROM sprint WHERE project_id = :project_id ORDER BY number ASC',
                ['project_id' => $project['id']],
            );

            $tickets = $this->connection->fetchAllAssociative(
                'SELECT id, project_id, sprint_id, title, description, status, reporter_id, assignee_id FROM ticket WHERE project_id = :project_id',
                ['project_id' => $project['id']],
            );
        }

        return [
            'team' => false !== $team ? $team : null,
            'project' => false !== $project ? $project : null,
            'members' => $members,
            'sprints' => $sprints,
            'tickets' => $tickets,
        ];
    }
}
