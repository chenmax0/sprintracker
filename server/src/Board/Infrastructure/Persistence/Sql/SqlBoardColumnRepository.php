<?php

declare(strict_types=1);

namespace App\Board\Infrastructure\Persistence\Sql;

use App\Board\Domain\BoardColumn;
use App\Board\Domain\BoardColumnRepositoryInterface;
use App\Board\Domain\ColumnId;
use App\Board\Domain\ProjectId;
use Doctrine\DBAL\Connection;

final class SqlBoardColumnRepository implements BoardColumnRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(ColumnId $id): ?BoardColumn
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, project_id, name, position FROM board_column WHERE id = :id',
            ['id' => (string) $id],
        );

        if (false === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function save(BoardColumn $column): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO board_column (id, project_id, name, position)
                VALUES (:id, :project_id, :name, :position)
                ON CONFLICT (id) DO UPDATE SET
                    name = EXCLUDED.name,
                    position = EXCLUDED.position
                SQL,
            [
                'id' => (string) $column->getId(),
                'project_id' => (string) $column->getProjectId(),
                'name' => $column->getName(),
                'position' => $column->getPosition(),
            ],
        );
    }

    public function delete(ColumnId $id): void
    {
        $this->connection->executeStatement('DELETE FROM board_column WHERE id = :id', ['id' => (string) $id]);
    }

    public function findByProjectId(ProjectId $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, project_id, name, position FROM board_column WHERE project_id = :project_id ORDER BY position ASC',
            ['project_id' => (string) $projectId],
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function nextPosition(ProjectId $projectId): int
    {
        $max = $this->connection->fetchOne(
            'SELECT MAX(position) FROM board_column WHERE project_id = :project_id',
            ['project_id' => (string) $projectId],
        );

        return null !== $max ? (int) $max + 1 : 0;
    }

    private function hydrate(array $row): BoardColumn
    {
        return BoardColumn::fromPersistence(
            new ColumnId($row['id']),
            new ProjectId($row['project_id']),
            $row['name'],
            (int) $row['position'],
        );
    }
}
