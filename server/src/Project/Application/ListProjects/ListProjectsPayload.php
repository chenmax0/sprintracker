<?php

declare(strict_types=1);

namespace App\Project\Application\ListProjects;

final class ListProjectsPayload
{
    public function __construct(
        public readonly string $teamId,
        public readonly string $requesterMemberId,
    ) {
    }
}
