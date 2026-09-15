<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTicketSprintHistory;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Application\Port\TicketSprintHistoryRepositoryInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class ListTicketSprintHistoryHandler
{
    public function __construct(
        private ListTicketSprintHistoryValidator $validator,
        private TicketRepositoryInterface $tickets,
        private TicketSprintHistoryRepositoryInterface $history,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    /**
     * @return list<TicketSprintHistoryEntry>
     */
    public function handle(ListTicketSprintHistoryPayload $payload): array
    {
        $this->validator->validate($payload);

        $ticket = $this->tickets->findById(new TicketId($payload->ticketId));

        if (null === $ticket) {
            throw new TicketNotFoundException($payload->ticketId);
        }

        if (!$this->projectTeamMembership->isMember((string) $ticket->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $this->history->findByTicketId($payload->ticketId);
    }
}
