<?php

declare(strict_types=1);

namespace App\Project\Application\DeleteProject;

final class DeleteProjectPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
    ) {
    }
}
