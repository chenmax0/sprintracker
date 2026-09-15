<?php

declare(strict_types=1);

namespace App\Project\Application\GetProject;

use Assert\Assert;

final class GetProjectValidator
{
    public function validate(GetProjectPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
