<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Persistence\Sql;

use App\Project\Application\Port\DefaultBoardColumnsSeederInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

final class SqlDefaultBoardColumnsSeeder implements DefaultBoardColumnsSeederInterface
{
    private const DEFAULT_NAMES = ['À faire', 'En cours', 'Terminé'];

    public function __construct(private Connection $connection)
    {
    }

    public function seedDefaults(string $projectId): void
    {
        foreach (self::DEFAULT_NAMES as $position => $name) {
            $this->connection->executeStatement(
                'INSERT INTO board_column (id, project_id, name, position) VALUES (:id, :project_id, :name, :position)',
                [
                    'id' => Uuid::v7()->toRfc4122(),
                    'project_id' => $projectId,
                    'name' => $name,
                    'position' => $position,
                ],
            );
        }
    }
}
