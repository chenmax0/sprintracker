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

final class Handler
{
    public function __construct(
        private Validator $validator,
        private TeamRepositoryInterface $teams,
        private TeamMembershipRepositoryInterface $memberships,
        private TeamIdGeneratorInterface $ids,
    ) {
    }

    public function handle(Payload $payload): Team
    {
        $this->validator->validate($payload);

        $team = Team::create($this->ids->generate(), $payload->name);
        $this->teams->save($team);

        $membership = TeamMembership::create(
            $team->getId(),
            new MemberId($payload->creatorMemberId),
            MemberRole::Owner,
        );
        $this->memberships->save($membership);

        return $team;
    }
}
