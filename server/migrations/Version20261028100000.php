<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track site activity: one row per unique visitor per UTC hour';
    }

    public function up(Schema $schema): void
    {
        // bucket_hour is the start of the UTC hour. The (visitor_id, bucket_hour)
        // primary key makes recording idempotent and keeps "unique visitors" a
        // plain COUNT(DISTINCT visitor_id) at any granularity (hour/day/week/...).
        // user_id is set once the visitor is (or later becomes) authenticated.
        $this->addSql('CREATE TABLE visitor_activity (visitor_id UUID NOT NULL, bucket_hour TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID DEFAULT NULL, PRIMARY KEY (visitor_id, bucket_hour))');
        $this->addSql('CREATE INDEX idx_visitor_activity_bucket_hour ON visitor_activity (bucket_hour)');
        $this->addSql('ALTER TABLE visitor_activity ADD CONSTRAINT fk_visitor_activity_user FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE visitor_activity');
    }
}
