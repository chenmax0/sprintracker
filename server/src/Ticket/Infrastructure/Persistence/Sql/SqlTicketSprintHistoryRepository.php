<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Application\ListTicketSprintHistory\TicketSprintHistoryEntry;
use App\Ticket\Application\Port\TicketSprintHistoryRepositoryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final class SqlTicketSprintHistoryRepository implements TicketSprintHistoryRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findByTicketId(string $ticketId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT sprint_id, recorded_at FROM ticket_sprint_history WHERE ticket_id = :ticket_id ORDER BY recorded_at ASC',
            ['ticket_id' => $ticketId],
        );

        return array_map(
            static fn (array $row) => new TicketSprintHistoryEntry($row['sprint_id'], new \DateTimeImmutable($row['recorded_at'])),
            $rows,
        );
    }

    public function countByTicketIds(array $ticketIds): array
    {
        if ([] === $ticketIds) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT ticket_id, COUNT(*) AS carried_over_count FROM ticket_sprint_history WHERE ticket_id IN (:ticket_ids) GROUP BY ticket_id',
            ['ticket_ids' => $ticketIds],
            ['ticket_ids' => ArrayParameterType::STRING],
        );

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['ticket_id']] = (int) $row['carried_over_count'];
        }

        return $counts;
    }
}
