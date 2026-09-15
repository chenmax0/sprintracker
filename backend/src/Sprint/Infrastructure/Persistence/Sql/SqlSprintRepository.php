<?php

declare(strict_types=1);

namespace App\Sprint\Infrastructure\Persistence\Sql;

use App\Sprint\Domain\ProjectId;
use App\Sprint\Domain\Sprint;
use App\Sprint\Domain\SprintId;
use App\Sprint\Domain\SprintRepositoryInterface;
use App\Sprint\Domain\SprintStatus;
use Doctrine\DBAL\Connection;

final class SqlSprintRepository implements SprintRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(SprintId $id): ?Sprint
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, project_id, number, start_date, end_date, status FROM sprint WHERE id = :id',
            ['id' => (string) $id],
        );

        if (false === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function save(Sprint $sprint): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO sprint (id, project_id, number, start_date, end_date, status)
                VALUES (:id, :project_id, :number, :start_date, :end_date, :status)
                ON CONFLICT (id) DO UPDATE SET
                    start_date = EXCLUDED.start_date,
                    end_date = EXCLUDED.end_date,
                    status = EXCLUDED.status
                SQL,
            [
                'id' => (string) $sprint->getId(),
                'project_id' => (string) $sprint->getProjectId(),
                'number' => $sprint->getNumber(),
                'start_date' => $sprint->getStartDate()->format('Y-m-d'),
                'end_date' => $sprint->getEndDate()->format('Y-m-d'),
                'status' => $sprint->getStatus()->value,
            ],
        );
    }

    public function nextSprintNumber(ProjectId $projectId): int
    {
        $max = $this->connection->fetchOne(
            'SELECT MAX(number) FROM sprint WHERE project_id = :project_id',
            ['project_id' => (string) $projectId],
        );

        return (int) $max + 1;
    }

    public function findByProjectId(ProjectId $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, project_id, number, start_date, end_date, status FROM sprint WHERE project_id = :project_id ORDER BY number ASC',
            ['project_id' => (string) $projectId],
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function hasActiveSprint(ProjectId $projectId): bool
    {
        $found = $this->connection->fetchOne(
            "SELECT 1 FROM sprint WHERE project_id = :project_id AND status = 'active'",
            ['project_id' => (string) $projectId],
        );

        return false !== $found;
    }

    private function hydrate(array $row): Sprint
    {
        return Sprint::fromPersistence(
            new SprintId($row['id']),
            new ProjectId($row['project_id']),
            (int) $row['number'],
            new \DateTimeImmutable($row['start_date']),
            new \DateTimeImmutable($row['end_date']),
            SprintStatus::from($row['status']),
        );
    }
}
