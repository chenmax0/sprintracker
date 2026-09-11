<?php

declare(strict_types=1);

namespace App\Team\Application\CreateTeam;

use Assert\Assert;

final class Validator
{
    public function validate(Payload $payload): void
    {
        Assert::lazy()
            ->that($payload->name, 'name')->notBlank()
            ->that($payload->creatorMemberId, 'creatorMemberId')->notBlank()
            ->verifyNow();
    }
}
