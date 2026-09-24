<?php

declare(strict_types=1);

namespace App\Ticket\Application\MoveTicketToColumn;

use App\Ticket\Application\Port\ColumnLookupInterface;
use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\ColumnId;
use App\Ticket\Domain\Exception\ColumnNotInProjectException;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use App\Ticket\Domain\TicketRepositoryInterface;

final class MoveTicketToColumnHandler
{
    public function __construct(
        private MoveTicketToColumnValidator $validator,
        private TicketRepositoryInterface $tickets,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
        private ColumnLookupInterface $columnLookup,
    ) {
    }

    public function handle(MoveTicketToColumnPayload $payload): Ticket
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

        if (!$this->columnLookup->belongsToProject($payload->columnId, $projectId)) {
            throw new ColumnNotInProjectException();
        }

        $ticket->moveToColumn(new ColumnId($payload->columnId));
        $this->tickets->save($ticket);

        return $ticket;
    }
}
