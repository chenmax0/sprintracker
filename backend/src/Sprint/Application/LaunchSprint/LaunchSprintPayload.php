<?php

declare(strict_types=1);

namespace App\Sprint\Application\LaunchSprint;

final class LaunchSprintPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
        public readonly string $startDate,
        public readonly string $endDate,
    ) {
    }
}
