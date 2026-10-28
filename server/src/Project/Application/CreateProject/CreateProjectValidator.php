<?php

declare(strict_types=1);

namespace App\Project\Application\CreateProject;

use Assert\Assert;

final class CreateProjectValidator
{
    public function validate(CreateProjectPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->teamId, 'teamId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->name, 'name')->notBlank()
            ->verifyNow();
    }
}
