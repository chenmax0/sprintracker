<?php

declare(strict_types=1);

namespace App\Project\Application\CreateProject;

final class CreateProjectPayload
{
    public function __construct(
        public readonly string $teamId,
        public readonly string $requesterMemberId,
        public readonly string $name,
    ) {
    }
}
