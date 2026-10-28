<?php

declare(strict_types=1);

namespace App\Team\Application\ListMyTeams;

use Assert\Assert;

final class ListMyTeamsValidator
{
    public function validate(ListMyTeamsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
