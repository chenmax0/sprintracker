<?php

declare(strict_types=1);

namespace App\Project\Application\GetProject;

final class GetProjectPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
    ) {
    }
}
