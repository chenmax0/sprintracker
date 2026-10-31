<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Application\ListBacklog\BacklogPage;
use App\Ticket\Application\ListBacklog\BacklogSort;
use App\Ticket\Application\ListBacklog\BacklogTicketView;
use App\Ticket\Application\ListBacklog\ListBacklogPayload;
use App\Ticket\Application\Port\BacklogQueryInterface;
use Doctrine\DBAL\Connection;

final class SqlBacklogQuery implements BacklogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function find(ListBacklogPayload $payload): BacklogPage
    {
        $conditions = [
            't.project_id = :project_id',
            <<<'SQL'
                (t.sprint_id IS NULL OR (
                    s.status = 'completed'
                    AND t.column_id != (SELECT id FROM board_column WHERE project_id = :project_id ORDER BY position DESC LIMIT 1)
                ))
                SQL,
        ];
        $params = ['project_id' => $payload->projectId];

        $search = null !== $payload->search ? trim($payload->search) : '';
        if ('' !== $search) {
            // "#12" or "12" also finds ticket 12; -1 never matches a number. ILIKE's
            // default escape character is the backslash, hence the addcslashes.
            $number = ltrim($search, '#');
            $conditions[] = '(t.title ILIKE :search OR t.number = :number)';
            $params['search'] = '%'.addcslashes($search, '%_\\').'%';
            $params['number'] = ctype_digit($number) ? (int) $number : -1;
        }

        if ('none' === $payload->assignee) {
            $conditions[] = 't.assignee_id IS NULL';
        } elseif (null !== $payload->assignee) {
            $conditions[] = 't.assignee_id = :assignee_id';
            $params['assignee_id'] = $payload->assignee;
        }

        $where = implode(' AND ', $conditions);
        $from = 'FROM ticket t LEFT JOIN sprint s ON s.id = t.sprint_id';

        $total = (int) $this->connection->fetchOne("SELECT COUNT(*) $from WHERE $where", $params);

        $orderBy = match (BacklogSort::from($payload->sort)) {
            BacklogSort::Oldest => 't.created_at ASC, t.number ASC',
            BacklogSort::Newest => 't.created_at DESC, t.number DESC',
            BacklogSort::MostCarriedOver => 'carried_over_count DESC, t.created_at ASC, t.number ASC',
        };

        $rows = $this->connection->fetchAllAssociative(
            "SELECT t.id, t.number, t.title, t.assignee_id, t.created_at,
                    (SELECT COUNT(*) FROM ticket_sprint_history h WHERE h.ticket_id = t.id) AS carried_over_count
             $from
             WHERE $where
             ORDER BY $orderBy
             LIMIT :limit OFFSET :offset",
            $params + [
                'limit' => $payload->perPage,
                'offset' => ($payload->page - 1) * $payload->perPage,
            ],
        );

        return new BacklogPage(
            array_map(
                static fn (array $row) => new BacklogTicketView(
                    $row['id'],
                    (int) $row['number'],
                    $row['title'],
                    $row['assignee_id'],
                    (new \DateTimeImmutable($row['created_at'], new \DateTimeZone('UTC')))->format(DATE_ATOM),
                    (int) $row['carried_over_count'],
                ),
                $rows,
            ),
            $total,
        );
    }
}
