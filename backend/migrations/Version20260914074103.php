<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260914074103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ticket and comment tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE ticket (
                id UUID NOT NULL,
                project_id UUID NOT NULL,
                sprint_id UUID DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                status VARCHAR(20) NOT NULL,
                reporter_id UUID NOT NULL,
                assignee_id UUID DEFAULT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_project FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_sprint FOREIGN KEY (sprint_id) REFERENCES sprint (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_reporter FOREIGN KEY (reporter_id) REFERENCES "user" (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_ticket_assignee FOREIGN KEY (assignee_id) REFERENCES "user" (id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE comment (
                id UUID NOT NULL,
                ticket_id UUID NOT NULL,
                author_id UUID NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_comment_ticket FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_comment_author FOREIGN KEY (author_id) REFERENCES "user" (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_comment_ticket');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_comment_author');
        $this->addSql('DROP TABLE comment');

        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_ticket_project');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_ticket_sprint');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_ticket_reporter');
        $this->addSql('ALTER TABLE ticket DROP CONSTRAINT FK_ticket_assignee');
        $this->addSql('DROP TABLE ticket');
    }
}
