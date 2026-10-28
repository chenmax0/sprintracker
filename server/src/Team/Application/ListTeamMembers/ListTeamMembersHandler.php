<?php

declare(strict_types=1);

namespace App\Team\Application\ListTeamMembers;

use App\Team\Application\Port\UserProfileLookupInterface;
use App\Team\Domain\Exception\NotATeamMemberException;
use App\Team\Domain\MemberId;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamMembershipRepositoryInterface;

final class ListTeamMembersHandler
{
    public function __construct(
        private ListTeamMembersValidator $validator,
        private TeamMembershipRepositoryInterface $memberships,
        private UserProfileLookupInterface $userProfiles,
    ) {
    }

    /**
     * @return list<TeamMemberView>
     */
    public function handle(ListTeamMembersPayload $payload): array
    {
        $this->validator->validate($payload);

        $teamId = new TeamId($payload->teamId);

        if (null === $this->memberships->findMembership($teamId, new MemberId($payload->requesterMemberId))) {
            throw new NotATeamMemberException();
        }

        $memberships = $this->memberships->findByTeamId($teamId);
        $profiles = $this->userProfiles->findByIds(array_map(
            static fn ($membership) => (string) $membership->getMemberId(),
            $memberships,
        ));

        return array_map(
            static function ($membership) use ($profiles) {
                $memberId = (string) $membership->getMemberId();
                $profile = $profiles[$memberId] ?? ['email' => '', 'name' => ''];

                return new TeamMemberView($memberId, $membership->getRole(), $profile['email'], $profile['name']);
            },
            $memberships,
        );
    }
}
