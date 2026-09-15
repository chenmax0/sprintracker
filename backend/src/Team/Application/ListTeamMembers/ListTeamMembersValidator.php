<?php

declare(strict_types=1);

namespace App\Team\Application\ListTeamMembers;

use Assert\Assert;

final class ListTeamMembersValidator
{
    public function validate(ListTeamMembersPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->teamId, 'teamId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
