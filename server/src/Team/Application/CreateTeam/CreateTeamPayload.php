<?php

declare(strict_types=1);

namespace App\Team\Application\CreateTeam;

final class CreateTeamPayload
{
    public function __construct(
        public readonly string $name,
        public readonly string $creatorMemberId,
    ) {
    }
}
