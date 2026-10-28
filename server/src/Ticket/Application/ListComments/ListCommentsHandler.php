<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListComments;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Comment;
use App\Ticket\Domain\CommentRepositoryInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class ListCommentsHandler
{
    public function __construct(
        private ListCommentsValidator $validator,
        private TicketRepositoryInterface $tickets,
        private CommentRepositoryInterface $comments,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    /**
     * @return list<Comment>
     */
    public function handle(ListCommentsPayload $payload): array
    {
        $this->validator->validate($payload);

        $ticketId = new TicketId($payload->ticketId);
        $ticket = $this->tickets->findById($ticketId);

        if (null === $ticket) {
            throw new TicketNotFoundException($payload->ticketId);
        }

        if (!$this->projectTeamMembership->isMember((string) $ticket->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $this->comments->findByTicketId($ticketId);
    }
}
