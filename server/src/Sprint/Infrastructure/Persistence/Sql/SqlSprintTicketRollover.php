<?php

declare(strict_types=1);

namespace App\Sprint\Infrastructure\Persistence\Sql;

use App\Sprint\Application\Port\SprintTicketRolloverInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

final class SqlSprintTicketRollover implements SprintTicketRolloverInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function rolloverIncompleteTickets(string $completedSprintId, string $nextSprintId): void
    {
        // "Done" has no fixed meaning now that columns are freely named and
        // ordered by the project: a ticket is considered finished once it
        // reached the board's rightmost column.
        $lastColumnId = $this->connection->fetchOne(
            <<<'SQL'
                SELECT bc.id
                FROM board_column bc
                WHERE bc.project_id = (SELECT project_id FROM sprint WHERE id = :sprint_id)
                ORDER BY bc.position DESC
                LIMIT 1
                SQL,
            ['sprint_id' => $completedSprintId],
        );

        $incompleteTicketIds = $this->connection->fetchFirstColumn(
            'SELECT id FROM ticket WHERE sprint_id = :sprint_id AND column_id != :last_column_id',
            ['sprint_id' => $completedSprintId, 'last_column_id' => $lastColumnId],
        );

        foreach ($incompleteTicketIds as $ticketId) {
            $this->connection->executeStatement(
                <<<'SQL'
                    INSERT INTO ticket_sprint_history (id, ticket_id, sprint_id, recorded_at)
                    VALUES (:id, :ticket_id, :sprint_id, :recorded_at)
                    ON CONFLICT (ticket_id, sprint_id) DO NOTHING
                    SQL,
                [
                    'id' => Uuid::v7()->toRfc4122(),
                    'ticket_id' => $ticketId,
                    'sprint_id' => $completedSprintId,
                    'recorded_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ],
            );
        }

        $this->connection->executeStatement(
            'UPDATE ticket SET sprint_id = :next_sprint_id WHERE sprint_id = :completed_sprint_id AND column_id != :last_column_id',
            ['next_sprint_id' => $nextSprintId, 'completed_sprint_id' => $completedSprintId, 'last_column_id' => $lastColumnId],
        );
    }
}
