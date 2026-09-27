<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260927100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow editing comments, tracked via a nullable updated_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD updated_at TIMESTAMP DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP COLUMN updated_at');
    }
}
