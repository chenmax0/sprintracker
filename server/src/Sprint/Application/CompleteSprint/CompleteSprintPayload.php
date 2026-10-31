<?php

declare(strict_types=1);

namespace App\Sprint\Application\CompleteSprint;

final class CompleteSprintPayload
{
    public function __construct(
        public readonly string $sprintId,
        public readonly string $requesterMemberId,
    ) {
    }
}
