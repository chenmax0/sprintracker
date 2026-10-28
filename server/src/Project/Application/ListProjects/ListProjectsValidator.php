<?php

declare(strict_types=1);

namespace App\Project\Application\ListProjects;

use Assert\Assert;

final class ListProjectsValidator
{
    public function validate(ListProjectsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->teamId, 'teamId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
