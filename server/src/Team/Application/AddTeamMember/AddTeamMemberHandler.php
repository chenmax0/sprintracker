<?php

declare(strict_types=1);

namespace App\Team\Application\AddTeamMember;

use App\Team\Application\Port\UserLookupInterface;
use App\Team\Domain\Exception\MemberAlreadyInTeamException;
use App\Team\Domain\Exception\MemberNotFoundException;
use App\Team\Domain\Exception\NotATeamMemberException;
use App\Team\Domain\Exception\TeamNotFoundException;
use App\Team\Domain\MemberId;
use App\Team\Domain\MemberRole;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamMembership;
use App\Team\Domain\TeamMembershipRepositoryInterface;
use App\Team\Domain\TeamRepositoryInterface;

final class AddTeamMemberHandler
{
    public function __construct(
        private AddTeamMemberValidator $validator,
        private TeamRepositoryInterface $teams,
        private TeamMembershipRepositoryInterface $memberships,
        private UserLookupInterface $userLookup,
    ) {
    }

    public function handle(AddTeamMemberPayload $payload): TeamMembership
    {
        $this->validator->validate($payload);

        $teamId = new TeamId($payload->teamId);

        if (null === $this->teams->findById($teamId)) {
            throw new TeamNotFoundException($payload->teamId);
        }

        $requesterId = new MemberId($payload->requesterMemberId);

        if (null === $this->memberships->findMembership($teamId, $requesterId)) {
            throw new NotATeamMemberException();
        }

        $newMemberUserId = $this->userLookup->findIdByEmail($payload->memberEmail);

        if (null === $newMemberUserId) {
            throw new MemberNotFoundException($payload->memberEmail);
        }

        $newMemberId = new MemberId($newMemberUserId);

        if (null !== $this->memberships->findMembership($teamId, $newMemberId)) {
            throw new MemberAlreadyInTeamException();
        }

        $membership = TeamMembership::create($teamId, $newMemberId, MemberRole::Member);
        $this->memberships->save($membership);

        return $membership;
    }
}
