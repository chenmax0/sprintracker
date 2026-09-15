<?php

declare(strict_types=1);

namespace App\Team\Application\ListTeamMembers;

use App\Team\Domain\Exception\NotATeamMemberException;
use App\Team\Domain\MemberId;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamMembership;
use App\Team\Domain\TeamMembershipRepositoryInterface;

final class ListTeamMembersHandler
{
    public function __construct(
        private ListTeamMembersValidator $validator,
        private TeamMembershipRepositoryInterface $memberships,
    ) {
    }

    /**
     * @return list<TeamMembership>
     */
    public function handle(ListTeamMembersPayload $payload): array
    {
        $this->validator->validate($payload);

        $teamId = new TeamId($payload->teamId);

        if (null === $this->memberships->findMembership($teamId, new MemberId($payload->requesterMemberId))) {
            throw new NotATeamMemberException();
        }

        return $this->memberships->findByTeamId($teamId);
    }
}
