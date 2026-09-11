<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Persistence\Sql;

use App\Team\Domain\MemberId;
use App\Team\Domain\MemberRole;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamMembership;
use App\Team\Domain\TeamMembershipRepositoryInterface;
use Doctrine\DBAL\Connection;

final class SqlTeamMembershipRepository implements TeamMembershipRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(TeamMembership $membership): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO team_membership (team_id, user_id, role)
                VALUES (:team_id, :user_id, :role)
                ON CONFLICT (team_id, user_id) DO UPDATE SET role = EXCLUDED.role
                SQL,
            [
                'team_id' => (string) $membership->getTeamId(),
                'user_id' => (string) $membership->getMemberId(),
                'role' => $membership->getRole()->value,
            ],
        );
    }

    public function findByTeamId(TeamId $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT team_id, user_id, role FROM team_membership WHERE team_id = :team_id',
            ['team_id' => (string) $teamId],
        );

        return array_map(
            static fn (array $row) => TeamMembership::fromPersistence(
                new TeamId($row['team_id']),
                new MemberId($row['user_id']),
                MemberRole::from($row['role']),
            ),
            $rows,
        );
    }
}
