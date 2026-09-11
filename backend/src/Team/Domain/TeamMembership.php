<?php

declare(strict_types=1);

namespace App\Team\Domain;

final class TeamMembership
{
    private function __construct(
        private TeamId $teamId,
        private MemberId $memberId,
        private MemberRole $role,
    ) {
    }

    public static function create(TeamId $teamId, MemberId $memberId, MemberRole $role): self
    {
        return new self($teamId, $memberId, $role);
    }

    /**
     * Reconstitutes a TeamMembership from persisted data. Only the persistence layer should call this.
     */
    public static function fromPersistence(TeamId $teamId, MemberId $memberId, MemberRole $role): self
    {
        return new self($teamId, $memberId, $role);
    }

    public function getTeamId(): TeamId
    {
        return $this->teamId;
    }

    public function getMemberId(): MemberId
    {
        return $this->memberId;
    }

    public function getRole(): MemberRole
    {
        return $this->role;
    }
}
