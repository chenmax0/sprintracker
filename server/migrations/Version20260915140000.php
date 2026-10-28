<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a short, per-project sequential ticket number';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket ADD number INT');

        // Backfill existing rows: UUIDv7 ids are time-ordered, so ordering by
        // id reproduces creation order without a created_at column to sort by.
        $this->addSql(<<<'SQL'
            UPDATE ticket
            SET number = numbered.rn
            FROM (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id ASC) AS rn
                FROM ticket
            ) AS numbered
            WHERE ticket.id = numbered.id
            SQL);

        $this->addSql('ALTER TABLE ticket ALTER COLUMN number SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ticket_project_number ON ticket (project_id, number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_ticket_project_number');
        $this->addSql('ALTER TABLE ticket DROP COLUMN number');
    }
}
