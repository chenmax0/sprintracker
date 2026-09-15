<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sprint lifecycle status and ticket sprint carry-over history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE sprint ADD status VARCHAR(20) NOT NULL DEFAULT 'active'");

        // Existing data may have several sprints per project (only the most
        // recent one, by number, should be active) before the "one active
        // sprint per project" constraint below can be added safely.
        $this->addSql(<<<'SQL'
            UPDATE sprint s
            SET status = 'completed'
            WHERE s.number < (SELECT MAX(s2.number) FROM sprint s2 WHERE s2.project_id = s.project_id)
            SQL);

        $this->addSql('CREATE UNIQUE INDEX UNIQ_sprint_active_per_project ON sprint (project_id) WHERE (status = \'active\')');

        $this->addSql(<<<'SQL'
            CREATE TABLE ticket_sprint_history (
                id UUID NOT NULL,
                ticket_id UUID NOT NULL,
                sprint_id UUID NOT NULL,
                recorded_at TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('ALTER TABLE ticket_sprint_history ADD CONSTRAINT FK_tsh_ticket FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE ticket_sprint_history ADD CONSTRAINT FK_tsh_sprint FOREIGN KEY (sprint_id) REFERENCES sprint (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_tsh_ticket_sprint ON ticket_sprint_history (ticket_id, sprint_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket_sprint_history DROP CONSTRAINT FK_tsh_ticket');
        $this->addSql('ALTER TABLE ticket_sprint_history DROP CONSTRAINT FK_tsh_sprint');
        $this->addSql('DROP TABLE ticket_sprint_history');

        $this->addSql('DROP INDEX UNIQ_sprint_active_per_project');
        $this->addSql('ALTER TABLE sprint DROP COLUMN status');
    }
}
