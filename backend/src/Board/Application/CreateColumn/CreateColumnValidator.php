<?php

declare(strict_types=1);

namespace App\Board\Application\CreateColumn;

use Assert\Assert;

final class CreateColumnValidator
{
    public function validate(CreateColumnPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->name, 'name')->notBlank()
            ->verifyNow();
    }
}
