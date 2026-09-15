<?php

declare(strict_types=1);

namespace App\Ticket\Application\UpdateTicketStatus;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;
use App\Ticket\Domain\TicketStatus;

final class UpdateTicketStatusHandler
{
    public function __construct(
        private UpdateTicketStatusValidator $validator,
        private TicketRepositoryInterface $tickets,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(UpdateTicketStatusPayload $payload): Ticket
    {
        $this->validator->validate($payload);

        $ticket = $this->tickets->findById(new TicketId($payload->ticketId));

        if (null === $ticket) {
            throw new TicketNotFoundException($payload->ticketId);
        }

        if (!$this->projectTeamMembership->isMember((string) $ticket->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $ticket->changeStatus(TicketStatus::from($payload->status));
        $this->tickets->save($ticket);

        return $ticket;
    }
}
