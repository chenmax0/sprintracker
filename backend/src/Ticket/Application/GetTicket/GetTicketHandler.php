<?php

declare(strict_types=1);

namespace App\Ticket\Application\GetTicket;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class GetTicketHandler
{
    public function __construct(
        private GetTicketValidator $validator,
        private TicketRepositoryInterface $tickets,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(GetTicketPayload $payload): Ticket
    {
        $this->validator->validate($payload);

        $ticket = $this->tickets->findById(new TicketId($payload->ticketId));

        if (null === $ticket) {
            throw new TicketNotFoundException($payload->ticketId);
        }

        if (!$this->projectTeamMembership->isMember((string) $ticket->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $ticket;
    }
}
