<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261031100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Record when a ticket was created, so the backlog can show and sort by age';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ticket ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        // Ticket ids are UUIDv7: their first 48 bits are the creation time in
        // milliseconds. Use it to backfill existing rows; ids that don't carry a
        // plausible timestamp (hand-written fixtures) fall back to now.
        $this->addSql(<<<'SQL'
            UPDATE ticket SET created_at = CASE
                WHEN to_timestamp(('x' || substr(replace(id::text, '-', ''), 1, 12))::bit(48)::bigint / 1000.0) > TIMESTAMP '2020-01-01'
                    THEN to_timestamp(('x' || substr(replace(id::text, '-', ''), 1, 12))::bit(48)::bigint / 1000.0) AT TIME ZONE 'UTC'
                ELSE now() AT TIME ZONE 'UTC'
            END
            SQL);
        $this->addSql("ALTER TABLE ticket ALTER COLUMN created_at SET DEFAULT (now() AT TIME ZONE 'UTC')");
        $this->addSql('ALTER TABLE ticket ALTER COLUMN created_at SET NOT NULL');
        $this->addSql('CREATE INDEX idx_ticket_project_created_at ON ticket (project_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_ticket_project_created_at');
        $this->addSql('ALTER TABLE ticket DROP COLUMN created_at');
    }
}
