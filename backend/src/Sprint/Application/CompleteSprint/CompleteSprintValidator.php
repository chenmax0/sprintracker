<?php

declare(strict_types=1);

namespace App\Sprint\Application\CompleteSprint;

use Assert\Assert;

final class CompleteSprintValidator
{
    public function validate(CompleteSprintPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->sprintId, 'sprintId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->nextStartDate, 'nextStartDate')->notBlank()->date('Y-m-d')
            ->that($payload->nextEndDate, 'nextEndDate')->notBlank()->date('Y-m-d')
            ->verifyNow();
    }
}
