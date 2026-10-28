<?php

declare(strict_types=1);

namespace App\Project\Application\RenameProject;

use Assert\Assert;

final class RenameProjectValidator
{
    public function validate(RenameProjectPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->name, 'name')->notBlank()
            ->verifyNow();
    }
}
