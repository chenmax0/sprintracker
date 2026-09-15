<?php

declare(strict_types=1);

namespace App\Team\Application\ListTeamMembers;

final class ListTeamMembersPayload
{
    public function __construct(
        public readonly string $teamId,
        public readonly string $requesterMemberId,
    ) {
    }
}
