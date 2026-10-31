<?php

declare(strict_types=1);

namespace App\Sprint\Application\CompleteSprint;

use App\Sprint\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Sprint\Domain\Exception\NotAProjectMemberException;
use App\Sprint\Domain\Exception\SprintNotFoundException;
use App\Sprint\Domain\Sprint;
use App\Sprint\Domain\SprintId;
use App\Sprint\Domain\SprintRepositoryInterface;

final class CompleteSprintHandler
{
    public function __construct(
        private CompleteSprintValidator $validator,
        private SprintRepositoryInterface $sprints,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(CompleteSprintPayload $payload): Sprint
    {
        $this->validator->validate($payload);

        $sprint = $this->sprints->findById(new SprintId($payload->sprintId));
        if (null === $sprint) {
            throw new SprintNotFoundException();
        }

        if (!$this->projectTeamMembership->isMember((string) $sprint->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $sprint->complete();
        $this->sprints->save($sprint);

        return $sprint;
    }
}
