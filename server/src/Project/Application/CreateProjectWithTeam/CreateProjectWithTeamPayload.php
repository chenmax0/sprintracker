<?php

declare(strict_types=1);

namespace App\Project\Application\CreateProjectWithTeam;

final class CreateProjectWithTeamPayload
{
    public function __construct(
        public readonly string $requesterMemberId,
        public readonly string $name,
    ) {
    }
}
