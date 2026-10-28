<?php

declare(strict_types=1);

namespace App\Team\Application\AddTeamMember;

use Assert\Assert;

final class AddTeamMemberValidator
{
    public function validate(AddTeamMemberPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->teamId, 'teamId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->memberEmail, 'memberEmail')->notBlank()->email()
            ->verifyNow();
    }
}
