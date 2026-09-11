<?php

declare(strict_types=1);

namespace App\Team\Application\AddTeamMember;

final class AddTeamMemberPayload
{
    public function __construct(
        public readonly string $teamId,
        public readonly string $requesterMemberId,
        public readonly string $memberEmail,
    ) {
    }
}
