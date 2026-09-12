<?php

declare(strict_types=1);

namespace App\Sprint\Infrastructure\Persistence\Sql;

use App\Sprint\Domain\ProjectId;
use App\Sprint\Domain\Sprint;
use App\Sprint\Domain\SprintId;
use App\Sprint\Domain\SprintRepositoryInterface;
use Doctrine\DBAL\Connection;

final class SqlSprintRepository implements SprintRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(SprintId $id): ?Sprint
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, project_id, number, start_date, end_date FROM sprint WHERE id = :id',
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
                INSERT INTO sprint (id, project_id, number, start_date, end_date)
                VALUES (:id, :project_id, :number, :start_date, :end_date)
                ON CONFLICT (id) DO UPDATE SET
                    start_date = EXCLUDED.start_date,
                    end_date = EXCLUDED.end_date
                SQL,
            [
                'id' => (string) $sprint->getId(),
                'project_id' => (string) $sprint->getProjectId(),
                'number' => $sprint->getNumber(),
                'start_date' => $sprint->getStartDate()->format('Y-m-d'),
                'end_date' => $sprint->getEndDate()->format('Y-m-d'),
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

    private function hydrate(array $row): Sprint
    {
        return Sprint::fromPersistence(
            new SprintId($row['id']),
            new ProjectId($row['project_id']),
            (int) $row['number'],
            new \DateTimeImmutable($row['start_date']),
            new \DateTimeImmutable($row['end_date']),
        );
    }
}
