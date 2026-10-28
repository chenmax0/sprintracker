<?php

declare(strict_types=1);

namespace App\Board\Application\RenameColumn;

use Assert\Assert;

final class RenameColumnValidator
{
    public function validate(RenameColumnPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->columnId, 'columnId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->name, 'name')->notBlank()
            ->verifyNow();
    }
}
