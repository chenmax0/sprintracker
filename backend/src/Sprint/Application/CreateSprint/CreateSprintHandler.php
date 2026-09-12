<?php

declare(strict_types=1);

namespace App\Sprint\Application\CreateSprint;

use App\Sprint\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Sprint\Application\Port\SprintIdGeneratorInterface;
use App\Sprint\Domain\Exception\NotAProjectMemberException;
use App\Sprint\Domain\ProjectId;
use App\Sprint\Domain\Sprint;
use App\Sprint\Domain\SprintRepositoryInterface;

final class CreateSprintHandler
{
    public function __construct(
        private CreateSprintValidator $validator,
        private SprintRepositoryInterface $sprints,
        private SprintIdGeneratorInterface $ids,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(CreateSprintPayload $payload): Sprint
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $projectId = new ProjectId($payload->projectId);
        $number = $this->sprints->nextSprintNumber($projectId);

        $sprint = Sprint::create(
            $this->ids->generate(),
            $projectId,
            $number,
            new \DateTimeImmutable($payload->startDate),
            new \DateTimeImmutable($payload->endDate),
        );
        $this->sprints->save($sprint);

        return $sprint;
    }
}
