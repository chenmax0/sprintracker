<?php

declare(strict_types=1);

namespace App\Ticket\Application\AddComment;

use App\Ticket\Application\Port\CommentIdGeneratorInterface;
use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Comment;
use App\Ticket\Domain\CommentRepositoryInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\MemberId;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class AddCommentHandler
{
    public function __construct(
        private AddCommentValidator $validator,
        private TicketRepositoryInterface $tickets,
        private CommentRepositoryInterface $comments,
        private CommentIdGeneratorInterface $ids,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(AddCommentPayload $payload): Comment
    {
        $this->validator->validate($payload);

        $ticketId = new TicketId($payload->ticketId);
        $ticket = $this->tickets->findById($ticketId);

        if (null === $ticket) {
            throw new TicketNotFoundException($payload->ticketId);
        }

        if (!$this->projectTeamMembership->isMember((string) $ticket->getProjectId(), $payload->authorMemberId)) {
            throw new NotAProjectMemberException();
        }

        $comment = Comment::create($this->ids->generate(), $ticketId, new MemberId($payload->authorMemberId), $payload->content);
        $this->comments->save($comment);

        return $comment;
    }
}
