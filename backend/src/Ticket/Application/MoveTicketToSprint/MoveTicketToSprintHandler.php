<?php

declare(strict_types=1);

namespace App\Ticket\Application\MoveTicketToSprint;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Application\Port\SprintLookupInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\SprintNotInProjectException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\SprintId;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class MoveTicketToSprintHandler
{
    public function __construct(
        private MoveTicketToSprintValidator $validator,
        private TicketRepositoryInterface $tickets,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
        private SprintLookupInterface $sprintLookup,
    ) {
    }

    public function handle(MoveTicketToSprintPayload $payload): Ticket
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

        $sprintId = null;
        if (null !== $payload->sprintId) {
            if (!$this->sprintLookup->belongsToProject($payload->sprintId, $projectId)) {
                throw new SprintNotInProjectException();
            }
            $sprintId = new SprintId($payload->sprintId);
        }

        $ticket->moveToSprint($sprintId);
        $this->tickets->save($ticket);

        return $ticket;
    }
}
