<?php

declare(strict_types=1);

namespace App\Project\Application\RenameProject;

final class RenameProjectPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
        public readonly string $name,
    ) {
    }
}
