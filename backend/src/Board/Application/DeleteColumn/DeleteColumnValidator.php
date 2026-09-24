<?php

declare(strict_types=1);

namespace App\Board\Application\DeleteColumn;

use Assert\Assert;

final class DeleteColumnValidator
{
    public function validate(DeleteColumnPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->columnId, 'columnId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
