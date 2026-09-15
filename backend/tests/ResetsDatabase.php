<?php

namespace App\Tests;

use Doctrine\DBAL\Connection;

/**
 * Wipes every table between tests, in FK-safe order (children before parents).
 * Update this list whenever a migration adds a new table.
 */
trait ResetsDatabase
{
    private function resetDatabase(Connection $connection): void
    {
        foreach (['ticket_sprint_history', 'comment', 'ticket', 'sprint', 'project', 'team_membership', 'team', '"user"'] as $table) {
            $connection->executeStatement("DELETE FROM $table");
        }
    }
}
