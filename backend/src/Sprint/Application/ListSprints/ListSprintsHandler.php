<?php

declare(strict_types=1);

namespace App\Sprint\Application\ListSprints;

use App\Sprint\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Sprint\Domain\Exception\NotAProjectMemberException;
use App\Sprint\Domain\ProjectId;
use App\Sprint\Domain\Sprint;
use App\Sprint\Domain\SprintRepositoryInterface;

final class ListSprintsHandler
{
    public function __construct(
        private ListSprintsValidator $validator,
        private SprintRepositoryInterface $sprints,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    /**
     * @return list<Sprint>
     */
    public function handle(ListSprintsPayload $payload): array
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $this->sprints->findByProjectId(new ProjectId($payload->projectId));
    }
}
