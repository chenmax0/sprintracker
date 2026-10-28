<?php

declare(strict_types=1);

namespace App\Project\Application\DeleteProject;

use App\Project\Application\Port\TeamOwnershipCheckerInterface;
use App\Project\Domain\Exception\NotTeamOwnerException;
use App\Project\Domain\Exception\ProjectNotFoundException;
use App\Project\Domain\ProjectId;
use App\Project\Domain\ProjectRepositoryInterface;

final class DeleteProjectHandler
{
    public function __construct(
        private DeleteProjectValidator $validator,
        private ProjectRepositoryInterface $projects,
        private TeamOwnershipCheckerInterface $teamOwnership,
    ) {
    }

    public function handle(DeleteProjectPayload $payload): void
    {
        $this->validator->validate($payload);

        $projectId = new ProjectId($payload->projectId);
        $project = $this->projects->findById($projectId);

        if (null === $project) {
            throw new ProjectNotFoundException($payload->projectId);
        }

        if (!$this->teamOwnership->isOwner((string) $project->getTeamId(), $payload->requesterMemberId)) {
            throw new NotTeamOwnerException();
        }

        $this->projects->delete($projectId);
    }
}
