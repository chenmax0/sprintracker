<?php

declare(strict_types=1);

namespace App\Project\Application\ListProjects;

use App\Project\Application\Port\TeamMembershipCheckerInterface;
use App\Project\Domain\Exception\NotATeamMemberException;
use App\Project\Domain\Project;
use App\Project\Domain\ProjectRepositoryInterface;
use App\Project\Domain\TeamId;

final class ListProjectsHandler
{
    public function __construct(
        private ListProjectsValidator $validator,
        private ProjectRepositoryInterface $projects,
        private TeamMembershipCheckerInterface $teamMembership,
    ) {
    }

    /**
     * @return list<Project>
     */
    public function handle(ListProjectsPayload $payload): array
    {
        $this->validator->validate($payload);

        if (!$this->teamMembership->isMember($payload->teamId, $payload->requesterMemberId)) {
            throw new NotATeamMemberException();
        }

        return $this->projects->findByTeamId(new TeamId($payload->teamId));
    }
}
