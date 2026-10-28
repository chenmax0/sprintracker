<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use Doctrine\DBAL\Connection;

final class SqlProjectTeamMembershipChecker implements ProjectTeamMembershipCheckerInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function isMember(string $projectId, string $memberId): bool
    {
        $found = $this->connection->fetchOne(
            <<<'SQL'
                SELECT 1
                FROM project p
                INNER JOIN team_membership tm ON tm.team_id = p.team_id
                WHERE p.id = :project_id AND tm.user_id = :member_id
                SQL,
            ['project_id' => $projectId, 'member_id' => $memberId],
        );

        return false !== $found;
    }
}
