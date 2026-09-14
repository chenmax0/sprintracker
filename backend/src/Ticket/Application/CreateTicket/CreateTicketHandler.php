<?php

declare(strict_types=1);

namespace App\Ticket\Application\CreateTicket;

use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Application\Port\SprintLookupInterface;
use App\Ticket\Application\Port\TicketIdGeneratorInterface;
use App\Ticket\Domain\Exception\AssigneeNotAProjectMemberException;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\SprintNotInProjectException;
use App\Ticket\Domain\MemberId;
use App\Ticket\Domain\ProjectId;
use App\Ticket\Domain\SprintId;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketRepositoryInterface;

final class CreateTicketHandler
{
    public function __construct(
        private CreateTicketValidator $validator,
        private TicketRepositoryInterface $tickets,
        private TicketIdGeneratorInterface $ids,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
        private SprintLookupInterface $sprintLookup,
    ) {
    }

    public function handle(CreateTicketPayload $payload): Ticket
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $sprintId = null;
        if (null !== $payload->sprintId) {
            if (!$this->sprintLookup->belongsToProject($payload->sprintId, $payload->projectId)) {
                throw new SprintNotInProjectException();
            }
            $sprintId = new SprintId($payload->sprintId);
        }

        $assigneeId = null;
        if (null !== $payload->assigneeId) {
            if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->assigneeId)) {
                throw new AssigneeNotAProjectMemberException();
            }
            $assigneeId = new MemberId($payload->assigneeId);
        }

        $ticket = Ticket::create(
            $this->ids->generate(),
            new ProjectId($payload->projectId),
            $sprintId,
            $payload->title,
            $payload->description,
            new MemberId($payload->requesterMemberId),
            $assigneeId,
        );
        $this->tickets->save($ticket);

        return $ticket;
    }
}
