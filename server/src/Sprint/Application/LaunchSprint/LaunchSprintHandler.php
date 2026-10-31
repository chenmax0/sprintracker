<?php

declare(strict_types=1);

namespace App\Sprint\Application\LaunchSprint;

use App\Sprint\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Sprint\Application\Port\SprintIdGeneratorInterface;
use App\Sprint\Application\Port\SprintTicketRolloverInterface;
use App\Sprint\Domain\Exception\NotAProjectMemberException;
use App\Sprint\Domain\Exception\SprintAlreadyActiveException;
use App\Sprint\Domain\ProjectId;
use App\Sprint\Domain\Sprint;
use App\Sprint\Domain\SprintRepositoryInterface;

final class LaunchSprintHandler
{
    public function __construct(
        private LaunchSprintValidator $validator,
        private SprintRepositoryInterface $sprints,
        private SprintIdGeneratorInterface $ids,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
        private SprintTicketRolloverInterface $rollover,
    ) {
    }

    public function handle(LaunchSprintPayload $payload): Sprint
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $projectId = new ProjectId($payload->projectId);

        if ($this->sprints->hasActiveSprint($projectId)) {
            throw new SprintAlreadyActiveException();
        }

        $number = $this->sprints->nextSprintNumber($projectId);

        $sprint = Sprint::launch(
            $this->ids->generate(),
            $projectId,
            $number,
            new \DateTimeImmutable($payload->startDate),
            new \DateTimeImmutable($payload->endDate),
        );
        $this->sprints->save($sprint);

        // Completing a sprint leaves its unfinished tickets in place; they move
        // on once the next sprint is actually started.
        $previous = $this->sprints->findLatestCompleted($projectId);
        if (null !== $previous) {
            $this->rollover->rolloverIncompleteTickets((string) $previous->getId(), (string) $sprint->getId());
        }

        return $sprint;
    }
}
