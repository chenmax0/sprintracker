<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

interface CommentRepositoryInterface
{
    public function findById(CommentId $id): ?Comment;

    public function save(Comment $comment): void;

    /**
     * @return list<Comment>
     */
    public function findByTicketId(TicketId $ticketId): array;
}
