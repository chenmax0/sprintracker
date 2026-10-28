<?php

declare(strict_types=1);

namespace App\Board\Application\ListColumns;

use Assert\Assert;

final class ListColumnsValidator
{
    public function validate(ListColumnsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
