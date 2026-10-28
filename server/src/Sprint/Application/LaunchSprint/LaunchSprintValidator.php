<?php

declare(strict_types=1);

namespace App\Sprint\Application\LaunchSprint;

use Assert\Assert;

final class LaunchSprintValidator
{
    public function validate(LaunchSprintPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->startDate, 'startDate')->notBlank()->date('Y-m-d')
            ->that($payload->endDate, 'endDate')->notBlank()->date('Y-m-d')
            ->verifyNow();
    }
}
