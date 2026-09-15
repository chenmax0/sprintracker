<?php

declare(strict_types=1);

namespace App\Team\Application\ListTeamMembers;

use App\Team\Domain\MemberRole;

final class TeamMemberView
{
    public function __construct(
        public readonly string $memberId,
        public readonly MemberRole $role,
        public readonly string $email,
        public readonly string $name,
    ) {
    }
}
