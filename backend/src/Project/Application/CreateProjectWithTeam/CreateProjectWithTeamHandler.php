<?php

declare(strict_types=1);

namespace App\Project\Application\CreateProjectWithTeam;

use App\Project\Application\CreateProject\CreateProjectHandler;
use App\Project\Application\CreateProject\CreateProjectPayload;
use App\Project\Domain\Project;
use App\Team\Application\CreateTeam\CreateTeamHandler;
use App\Team\Application\CreateTeam\CreateTeamPayload;

/**
 * The product has no user-facing notion of "team": creating a project
 * transparently creates a same-named team behind it (the requester becomes
 * its owner) and the project inside it. This composes the two contexts'
 * existing use cases at the Application layer - Team's Domain classes are
 * still never touched from here, only its public CreateTeamHandler.
 */
final class CreateProjectWithTeamHandler
{
    public function __construct(
        private CreateProjectWithTeamValidator $validator,
        private CreateTeamHandler $createTeam,
        private CreateProjectHandler $createProject,
    ) {
    }

    public function handle(CreateProjectWithTeamPayload $payload): Project
    {
        $this->validator->validate($payload);

        $team = $this->createTeam->handle(new CreateTeamPayload($payload->name, $payload->requesterMemberId));

        return $this->createProject->handle(
            new CreateProjectPayload((string) $team->getId(), $payload->requesterMemberId, $payload->name),
        );
    }
}
