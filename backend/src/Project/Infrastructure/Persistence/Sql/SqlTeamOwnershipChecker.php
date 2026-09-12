<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Persistence\Sql;

use App\Project\Application\Port\TeamOwnershipCheckerInterface;
use Doctrine\DBAL\Connection;

final class SqlTeamOwnershipChecker implements TeamOwnershipCheckerInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function isOwner(string $teamId, string $memberId): bool
    {
        $role = $this->connection->fetchOne(
            'SELECT role FROM team_membership WHERE team_id = :team_id AND user_id = :member_id',
            ['team_id' => $teamId, 'member_id' => $memberId],
        );

        return 'owner' === $role;
    }
}
