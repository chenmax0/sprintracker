<?php

declare(strict_types=1);

namespace App\Project\Application\CreateProject;

use App\Project\Application\Port\DefaultBoardColumnsSeederInterface;
use App\Project\Application\Port\ProjectIdGeneratorInterface;
use App\Project\Application\Port\TeamOwnershipCheckerInterface;
use App\Project\Domain\Exception\NotTeamOwnerException;
use App\Project\Domain\Project;
use App\Project\Domain\ProjectRepositoryInterface;
use App\Project\Domain\TeamId;

final class CreateProjectHandler
{
    public function __construct(
        private CreateProjectValidator $validator,
        private ProjectRepositoryInterface $projects,
        private ProjectIdGeneratorInterface $ids,
        private TeamOwnershipCheckerInterface $teamOwnership,
        private DefaultBoardColumnsSeederInterface $boardColumns,
    ) {
    }

    public function handle(CreateProjectPayload $payload): Project
    {
        $this->validator->validate($payload);

        if (!$this->teamOwnership->isOwner($payload->teamId, $payload->requesterMemberId)) {
            throw new NotTeamOwnerException();
        }

        $project = Project::create($this->ids->generate(), new TeamId($payload->teamId), $payload->name);
        $this->projects->save($project);
        $this->boardColumns->seedDefaults((string) $project->getId());

        return $project;
    }
}
