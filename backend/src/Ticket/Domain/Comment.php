<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

final class Comment
{
    private function __construct(
        private CommentId $id,
        private TicketId $ticketId,
        private MemberId $authorId,
        private string $content,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(CommentId $id, TicketId $ticketId, MemberId $authorId, string $content): self
    {
        return new self($id, $ticketId, $authorId, $content, new \DateTimeImmutable());
    }

    public static function fromPersistence(
        CommentId $id,
        TicketId $ticketId,
        MemberId $authorId,
        string $content,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $ticketId, $authorId, $content, $createdAt);
    }

    public function getId(): CommentId
    {
        return $this->id;
    }

    public function getTicketId(): TicketId
    {
        return $this->ticketId;
    }

    public function getAuthorId(): MemberId
    {
        return $this->authorId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
