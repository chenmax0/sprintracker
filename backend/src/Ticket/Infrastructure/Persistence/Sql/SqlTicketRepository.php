<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Domain\MemberId;
use App\Ticket\Domain\ProjectId;
use App\Ticket\Domain\SprintId;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;
use App\Ticket\Domain\TicketStatus;
use Doctrine\DBAL\Connection;

final class SqlTicketRepository implements TicketRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(TicketId $id): ?Ticket
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, project_id, number, sprint_id, title, description, status, reporter_id, assignee_id FROM ticket WHERE id = :id',
            ['id' => (string) $id],
        );

        if (false === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function save(Ticket $ticket): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO ticket (id, project_id, number, sprint_id, title, description, status, reporter_id, assignee_id)
                VALUES (:id, :project_id, :number, :sprint_id, :title, :description, :status, :reporter_id, :assignee_id)
                ON CONFLICT (id) DO UPDATE SET
                    sprint_id = EXCLUDED.sprint_id,
                    title = EXCLUDED.title,
                    description = EXCLUDED.description,
                    status = EXCLUDED.status,
                    assignee_id = EXCLUDED.assignee_id
                SQL,
            [
                'id' => (string) $ticket->getId(),
                'project_id' => (string) $ticket->getProjectId(),
                'number' => $ticket->getNumber(),
                'sprint_id' => null !== $ticket->getSprintId() ? (string) $ticket->getSprintId() : null,
                'title' => $ticket->getTitle(),
                'description' => $ticket->getDescription(),
                'status' => $ticket->getStatus()->value,
                'reporter_id' => (string) $ticket->getReporterId(),
                'assignee_id' => null !== $ticket->getAssigneeId() ? (string) $ticket->getAssigneeId() : null,
            ],
        );
    }

    public function findByProjectId(ProjectId $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, project_id, number, sprint_id, title, description, status, reporter_id, assignee_id FROM ticket WHERE project_id = :project_id',
            ['project_id' => (string) $projectId],
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function nextTicketNumber(ProjectId $projectId): int
    {
        $max = $this->connection->fetchOne(
            'SELECT MAX(number) FROM ticket WHERE project_id = :project_id',
            ['project_id' => (string) $projectId],
        );

        return (int) $max + 1;
    }

    private function hydrate(array $row): Ticket
    {
        return Ticket::fromPersistence(
            new TicketId($row['id']),
            new ProjectId($row['project_id']),
            (int) $row['number'],
            null !== $row['sprint_id'] ? new SprintId($row['sprint_id']) : null,
            $row['title'],
            $row['description'],
            TicketStatus::from($row['status']),
            new MemberId($row['reporter_id']),
            null !== $row['assignee_id'] ? new MemberId($row['assignee_id']) : null,
        );
    }
}
