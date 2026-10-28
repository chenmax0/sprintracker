<?php

declare(strict_types=1);

namespace App\Project\Application\RenameProject;

use App\Project\Application\Port\TeamOwnershipCheckerInterface;
use App\Project\Domain\Exception\NotTeamOwnerException;
use App\Project\Domain\Exception\ProjectNotFoundException;
use App\Project\Domain\Project;
use App\Project\Domain\ProjectId;
use App\Project\Domain\ProjectRepositoryInterface;

final class RenameProjectHandler
{
    public function __construct(
        private RenameProjectValidator $validator,
        private ProjectRepositoryInterface $projects,
        private TeamOwnershipCheckerInterface $teamOwnership,
    ) {
    }

    public function handle(RenameProjectPayload $payload): Project
    {
        $this->validator->validate($payload);

        $project = $this->projects->findById(new ProjectId($payload->projectId));

        if (null === $project) {
            throw new ProjectNotFoundException($payload->projectId);
        }

        if (!$this->teamOwnership->isOwner((string) $project->getTeamId(), $payload->requesterMemberId)) {
            throw new NotTeamOwnerException();
        }

        $project->rename($payload->name);
        $this->projects->save($project);

        return $project;
    }
}
