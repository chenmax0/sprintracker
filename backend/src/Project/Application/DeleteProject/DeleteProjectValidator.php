<?php

declare(strict_types=1);

namespace App\Project\Application\DeleteProject;

use Assert\Assert;

final class DeleteProjectValidator
{
    public function validate(DeleteProjectPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
