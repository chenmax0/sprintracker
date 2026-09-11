<?php

declare(strict_types=1);

namespace App\Team\Application\CreateTeam;

use App\Team\Application\Port\TeamIdGeneratorInterface;
use App\Team\Domain\MemberId;
use App\Team\Domain\MemberRole;
use App\Team\Domain\Team;
use App\Team\Domain\TeamMembership;
use App\Team\Domain\TeamMembershipRepositoryInterface;
use App\Team\Domain\TeamRepositoryInterface;

final class CreateTeamHandler
{
    public function __construct(
        private TeamRepositoryInterface $teams,
        private TeamMembershipRepositoryInterface $memberships,
        private TeamIdGeneratorInterface $ids,
    ) {
    }

    public function __invoke(CreateTeamCommand $command): Team
    {
        $team = Team::create($this->ids->generate(), $command->name);
        $this->teams->save($team);

        $membership = TeamMembership::create(
            $team->getId(),
            new MemberId($command->creatorMemberId),
            MemberRole::Owner,
        );
        $this->memberships->save($membership);

        return $team;
    }
}
