<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Persistence\Sql;

use App\Ticket\Domain\Comment;
use App\Ticket\Domain\CommentId;
use App\Ticket\Domain\CommentRepositoryInterface;
use App\Ticket\Domain\MemberId;
use App\Ticket\Domain\TicketId;
use Doctrine\DBAL\Connection;

final class SqlCommentRepository implements CommentRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(Comment $comment): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                INSERT INTO comment (id, ticket_id, author_id, content, created_at)
                VALUES (:id, :ticket_id, :author_id, :content, :created_at)
                SQL,
            [
                'id' => (string) $comment->getId(),
                'ticket_id' => (string) $comment->getTicketId(),
                'author_id' => (string) $comment->getAuthorId(),
                'content' => $comment->getContent(),
                'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function findByTicketId(TicketId $ticketId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, ticket_id, author_id, content, created_at FROM comment WHERE ticket_id = :ticket_id ORDER BY created_at ASC',
            ['ticket_id' => (string) $ticketId],
        );

        return array_map(
            static fn (array $row) => Comment::fromPersistence(
                new CommentId($row['id']),
                new TicketId($row['ticket_id']),
                new MemberId($row['author_id']),
                $row['content'],
                new \DateTimeImmutable($row['created_at']),
            ),
            $rows,
        );
    }
}
