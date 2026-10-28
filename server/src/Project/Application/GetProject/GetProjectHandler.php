<?php

declare(strict_types=1);

namespace App\Project\Application\GetProject;

use App\Project\Application\Port\TeamMembershipCheckerInterface;
use App\Project\Domain\Exception\NotATeamMemberException;
use App\Project\Domain\Exception\ProjectNotFoundException;
use App\Project\Domain\Project;
use App\Project\Domain\ProjectId;
use App\Project\Domain\ProjectRepositoryInterface;

final class GetProjectHandler
{
    public function __construct(
        private GetProjectValidator $validator,
        private ProjectRepositoryInterface $projects,
        private TeamMembershipCheckerInterface $teamMembership,
    ) {
    }

    public function handle(GetProjectPayload $payload): Project
    {
        $this->validator->validate($payload);

        $project = $this->projects->findById(new ProjectId($payload->projectId));

        if (null === $project) {
            throw new ProjectNotFoundException($payload->projectId);
        }

        if (!$this->teamMembership->isMember((string) $project->getTeamId(), $payload->requesterMemberId)) {
            throw new NotATeamMemberException();
        }

        return $project;
    }
}
