<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seeds a fixed-id demo team/project/sprint/tickets, used by the public,
 * unauthenticated GET /api/demo endpoint. Idempotent (ON CONFLICT DO NOTHING)
 * so it's safe to re-run.
 */
final class Version20260915083728 extends AbstractMigration
{
    private const DEMO_USER_ID = '00000000-0000-7000-8000-000000000001';
    private const DEMO_TEAM_ID = '00000000-0000-7000-8000-000000000002';
    private const DEMO_PROJECT_ID = '00000000-0000-7000-8000-000000000003';
    private const DEMO_SPRINT_ID = '00000000-0000-7000-8000-000000000004';

    public function getDescription(): string
    {
        return 'Seed demo team/project/sprint/tickets for the public demo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO "user" (id, email, name, password, roles) VALUES (:id, :email, :name, :password, :roles) ON CONFLICT (id) DO NOTHING',
            [
                'id' => self::DEMO_USER_ID,
                'email' => 'demo@sprintracker.app',
                'name' => 'Demo',
                // Not a real credential: this account is never logged into, it only
                // exists to satisfy the FK columns (reporter_id, assignee_id, ...).
                'password' => '$2y$12$FE/DFqj2kI8UTotvcVvGEeWS8QvyPa2sONV3.4NFRCjrKVVoQa9S6',
                'roles' => '["ROLE_USER"]',
            ],
        );

        $this->addSql(
            'INSERT INTO team (id, name) VALUES (:id, :name) ON CONFLICT (id) DO NOTHING',
            ['id' => self::DEMO_TEAM_ID, 'name' => 'Sprintracker Demo'],
        );

        $this->addSql(
            'INSERT INTO team_membership (team_id, user_id, role) VALUES (:team_id, :user_id, :role) ON CONFLICT (team_id, user_id) DO NOTHING',
            ['team_id' => self::DEMO_TEAM_ID, 'user_id' => self::DEMO_USER_ID, 'role' => 'owner'],
        );

        $this->addSql(
            'INSERT INTO project (id, team_id, name) VALUES (:id, :team_id, :name) ON CONFLICT (id) DO NOTHING',
            ['id' => self::DEMO_PROJECT_ID, 'team_id' => self::DEMO_TEAM_ID, 'name' => 'Site vitrine'],
        );

        $this->addSql(
            'INSERT INTO sprint (id, project_id, number, start_date, end_date) VALUES (:id, :project_id, 1, :start_date, :end_date) ON CONFLICT (id) DO NOTHING',
            ['id' => self::DEMO_SPRINT_ID, 'project_id' => self::DEMO_PROJECT_ID, 'start_date' => '2026-09-01', 'end_date' => '2026-09-15'],
        );

        $tickets = [
            ['id' => '00000000-0000-7000-8000-000000000005', 'sprint_id' => self::DEMO_SPRINT_ID, 'title' => 'Mettre en place le design system', 'status' => 'done', 'assignee_id' => self::DEMO_USER_ID],
            ['id' => '00000000-0000-7000-8000-000000000006', 'sprint_id' => self::DEMO_SPRINT_ID, 'title' => 'Page de connexion', 'status' => 'in_progress', 'assignee_id' => self::DEMO_USER_ID],
            ['id' => '00000000-0000-7000-8000-000000000007', 'sprint_id' => null, 'title' => 'Notifications par email', 'status' => 'todo', 'assignee_id' => null],
            ['id' => '00000000-0000-7000-8000-000000000008', 'sprint_id' => null, 'title' => 'Export CSV des tickets', 'status' => 'todo', 'assignee_id' => null],
        ];

        foreach ($tickets as $ticket) {
            $this->addSql(
                'INSERT INTO ticket (id, project_id, sprint_id, title, description, status, reporter_id, assignee_id) VALUES (:id, :project_id, :sprint_id, :title, NULL, :status, :reporter_id, :assignee_id) ON CONFLICT (id) DO NOTHING',
                [
                    'id' => $ticket['id'],
                    'project_id' => self::DEMO_PROJECT_ID,
                    'sprint_id' => $ticket['sprint_id'],
                    'title' => $ticket['title'],
                    'status' => $ticket['status'],
                    'reporter_id' => self::DEMO_USER_ID,
                    'assignee_id' => $ticket['assignee_id'],
                ],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM ticket WHERE project_id = :project_id', ['project_id' => self::DEMO_PROJECT_ID]);
        $this->addSql('DELETE FROM sprint WHERE id = :id', ['id' => self::DEMO_SPRINT_ID]);
        $this->addSql('DELETE FROM project WHERE id = :id', ['id' => self::DEMO_PROJECT_ID]);
        $this->addSql('DELETE FROM team_membership WHERE team_id = :id', ['id' => self::DEMO_TEAM_ID]);
        $this->addSql('DELETE FROM team WHERE id = :id', ['id' => self::DEMO_TEAM_ID]);
        $this->addSql('DELETE FROM "user" WHERE id = :id', ['id' => self::DEMO_USER_ID]);
    }
}
