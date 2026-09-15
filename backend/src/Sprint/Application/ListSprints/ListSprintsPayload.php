<?php

declare(strict_types=1);

namespace App\Sprint\Application\ListSprints;

final class ListSprintsPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
    ) {
    }
}
