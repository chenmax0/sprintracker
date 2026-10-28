<?php

declare(strict_types=1);

namespace App\Team\Application\GetTeam;

use App\Team\Domain\Exception\NotATeamMemberException;
use App\Team\Domain\Exception\TeamNotFoundException;
use App\Team\Domain\MemberId;
use App\Team\Domain\Team;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamMembershipRepositoryInterface;
use App\Team\Domain\TeamRepositoryInterface;

final class GetTeamHandler
{
    public function __construct(
        private GetTeamValidator $validator,
        private TeamRepositoryInterface $teams,
        private TeamMembershipRepositoryInterface $memberships,
    ) {
    }

    public function handle(GetTeamPayload $payload): Team
    {
        $this->validator->validate($payload);

        $teamId = new TeamId($payload->teamId);
        $team = $this->teams->findById($teamId);

        if (null === $team) {
            throw new TeamNotFoundException($payload->teamId);
        }

        if (null === $this->memberships->findMembership($teamId, new MemberId($payload->requesterMemberId))) {
            throw new NotATeamMemberException();
        }

        return $team;
    }
}
