<?php

declare(strict_types=1);

namespace App\Sprint\Application\ListSprints;

use Assert\Assert;

final class ListSprintsValidator
{
    public function validate(ListSprintsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
