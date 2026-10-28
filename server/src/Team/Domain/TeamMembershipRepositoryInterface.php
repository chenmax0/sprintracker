<?php

declare(strict_types=1);

namespace App\Team\Domain;

interface TeamMembershipRepositoryInterface
{
    public function save(TeamMembership $membership): void;

    public function findMembership(TeamId $teamId, MemberId $memberId): ?TeamMembership;

    /**
     * @return list<TeamMembership>
     */
    public function findByTeamId(TeamId $teamId): array;
}
