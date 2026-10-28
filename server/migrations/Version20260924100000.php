<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Replaces the fixed todo/in_progress/done ticket status with per-project,
 * freely named and ordered kanban columns. "Done" is no longer a status
 * value - it's whichever column ends up rightmost on a project's board (see
 * Sprint\Infrastructure\Persistence\Sql\SqlSprintTicketRollover).
 */
final class Version20260924100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace fixed ticket status with customizable per-project board columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE board_column (
                id UUID NOT NULL,
                project_id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('ALTER TABLE board_column ADD CONSTRAINT FK_board_column_project FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_board_column_project_position ON board_column (project_id, position)');

        // Every existing project gets the same 3 default columns the app
        // used to hardcode, in the same order, so existing boards look
        // identical right after this migration.
        $this->addSql(<<<'SQL'
            INSERT INTO board_column (id, project_id, name, position)
            SELECT gen_random_uuid(), p.id, defaults.name, defaults.position
            FROM project p
            CROSS JOIN (VALUES ('À faire', 0), ('En cours', 1), ('Terminé', 2)) AS defaults(name, position)
            SQL);

        $this->addSql('ALTER TABLE ticket ADD column_id UUID');
        $this->addSql(<<<'SQL'
            UPDATE ticket t
            SET column_id = bc.id
            FROM board_column bc
            WHERE bc.project_id = t.project_id
              AND bc.position = CASE t.status WHEN 'todo' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'done' THEN 2 END
            SQL);
        $this->addSql('ALTER TABLE ticket ALTER COLUMN column_id SET NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_column FOREIGN KEY (column_id) REFERENCES board_column (id)');
        $this->addSql('ALTER TABLE ticket DROP COLUMN status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ticket ADD status VARCHAR(20)");
        $this->addSql(<<<'SQL'
            UPDATE ticket t
            SET status = CASE bc.position WHEN 0 THEN 'todo' WHEN 1 THEN 'in_progress' ELSE 'done' END
            FROM board_column bc
            WHERE bc.id = t.column_id
            SQL);
        $this->addSql("ALTER TABLE ticket ALTER COLUMN status SET NOT NULL");
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_ticket_column');
        $this->addSql('ALTER TABLE ticket DROP COLUMN column_id');

        $this->addSql('ALTER TABLE board_column DROP CONSTRAINT FK_board_column_project');
        $this->addSql('DROP TABLE board_column');
    }
}
