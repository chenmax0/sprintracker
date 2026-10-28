<?php

declare(strict_types=1);

namespace App\Team\Application\GetTeam;

final class GetTeamPayload
{
    public function __construct(
        public readonly string $teamId,
        public readonly string $requesterMemberId,
    ) {
    }
}
