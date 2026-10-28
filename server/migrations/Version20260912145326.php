<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260912145326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sprint table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE sprint (
                id UUID NOT NULL,
                project_id UUID NOT NULL,
                number INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('ALTER TABLE sprint ADD CONSTRAINT FK_sprint_project FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_sprint_project_number ON sprint (project_id, number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sprint DROP CONSTRAINT FK_sprint_project');
        $this->addSql('DROP TABLE sprint');
    }
}
