<?php

declare(strict_types=1);

namespace App\Sprint\Application\CreateSprint;

use Assert\Assert;

final class CreateSprintValidator
{
    public function validate(CreateSprintPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->startDate, 'startDate')->notBlank()->date('Y-m-d')
            ->that($payload->endDate, 'endDate')->notBlank()->date('Y-m-d')
            ->verifyNow();
    }
}
