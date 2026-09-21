<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Persistence\Sql;

use App\Project\Application\ListMyProjects\MyProjectView;
use App\Project\Application\Port\MyProjectsQueryInterface;
use Doctrine\DBAL\Connection;

final class SqlMyProjectsQuery implements MyProjectsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findForMember(string $memberId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT p.id, p.team_id, t.name AS team_name, p.name, tm.role
             FROM project p
             INNER JOIN team_membership tm ON tm.team_id = p.team_id
             INNER JOIN team t ON t.id = p.team_id
             WHERE tm.user_id = :member_id
             ORDER BY t.name ASC, p.name ASC',
            ['member_id' => $memberId],
        );

        return array_map(
            static fn (array $row) => new MyProjectView(
                $row['id'],
                $row['team_id'],
                $row['team_name'],
                $row['name'],
                'owner' === $row['role'],
            ),
            $rows,
        );
    }
}
