<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260911220436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add team and team_membership tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE team (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE team_membership (team_id UUID NOT NULL, user_id UUID NOT NULL, role VARCHAR(20) NOT NULL, PRIMARY KEY (team_id, user_id))');
        $this->addSql('ALTER TABLE team_membership ADD CONSTRAINT FK_team_membership_team FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_membership ADD CONSTRAINT FK_team_membership_user FOREIGN KEY (user_id) REFERENCES "user" (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_membership DROP CONSTRAINT FK_team_membership_team');
        $this->addSql('ALTER TABLE team_membership DROP CONSTRAINT FK_team_membership_user');
        $this->addSql('DROP TABLE team_membership');
        $this->addSql('DROP TABLE team');
    }
}
