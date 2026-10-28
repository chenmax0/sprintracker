<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListTickets;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\ProjectId;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketRepositoryInterface;

final class ListTicketsHandler
{
    public function __construct(
        private ListTicketsValidator $validator,
        private TicketRepositoryInterface $tickets,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    /**
     * @return list<Ticket>
     */
    public function handle(ListTicketsPayload $payload): array
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $this->tickets->findByProjectId(new ProjectId($payload->projectId));
    }
}
