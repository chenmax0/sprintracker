<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListBacklog;

use App\Ticket\Application\Port\BacklogQueryInterface;
use App\Ticket\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Ticket\Domain\Exception\NotAProjectMemberException;

final class ListBacklogHandler
{
    public function __construct(
        private ListBacklogValidator $validator,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
        private BacklogQueryInterface $backlog,
    ) {
    }

    public function handle(ListBacklogPayload $payload): BacklogPage
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $this->backlog->find($payload);
    }
}
