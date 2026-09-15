<?php

declare(strict_types=1);

namespace App\Team\Application\GetTeam;

use Assert\Assert;

final class GetTeamValidator
{
    public function validate(GetTeamPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->teamId, 'teamId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
