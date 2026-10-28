<?php

declare(strict_types=1);

namespace App\Ticket\Application\AssignTicket;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Exception\AssigneeNotAProjectMemberException;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\MemberId;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class AssignTicketHandler
{
    public function __construct(
        private AssignTicketValidator $validator,
        private TicketRepositoryInterface $tickets,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(AssignTicketPayload $payload): Ticket
    {
        $this->validator->validate($payload);

        $ticket = $this->tickets->findById(new TicketId($payload->ticketId));

        if (null === $ticket) {
            throw new TicketNotFoundException($payload->ticketId);
        }

        $projectId = (string) $ticket->getProjectId();

        if (!$this->projectTeamMembership->isMember($projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $assigneeId = null;
        if (null !== $payload->assigneeId) {
            if (!$this->projectTeamMembership->isMember($projectId, $payload->assigneeId)) {
                throw new AssigneeNotAProjectMemberException();
            }
            $assigneeId = new MemberId($payload->assigneeId);
        }

        $ticket->assignTo($assigneeId);
        $this->tickets->save($ticket);

        return $ticket;
    }
}
