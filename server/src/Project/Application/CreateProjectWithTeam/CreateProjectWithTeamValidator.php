<?php

declare(strict_types=1);

namespace App\Project\Application\CreateProjectWithTeam;

use Assert\Assert;

final class CreateProjectWithTeamValidator
{
    public function validate(CreateProjectWithTeamPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->name, 'name')->notBlank()
            ->verifyNow();
    }
}
