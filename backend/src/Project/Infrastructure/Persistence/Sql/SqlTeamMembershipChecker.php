<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Persistence\Sql;

use App\Project\Application\Port\TeamMembershipCheckerInterface;
use Doctrine\DBAL\Connection;

final class SqlTeamMembershipChecker implements TeamMembershipCheckerInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function isMember(string $teamId, string $memberId): bool
    {
        $found = $this->connection->fetchOne(
            'SELECT 1 FROM team_membership WHERE team_id = :team_id AND user_id = :member_id',
            ['team_id' => $teamId, 'member_id' => $memberId],
        );

        return false !== $found;
    }
}
