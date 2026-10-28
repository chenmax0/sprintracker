<?php

declare(strict_types=1);

namespace App\Board\Application\ReorderColumns;

use Assert\Assert;

final class ReorderColumnsValidator
{
    public function validate(ReorderColumnsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->orderedColumnIds, 'orderedColumnIds')->notEmpty()
            ->verifyNow();
    }
}
