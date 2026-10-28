<?php

declare(strict_types=1);

namespace App\Project\Application\ListMyProjects;

use Assert\Assert;

final class ListMyProjectsValidator
{
    public function validate(ListMyProjectsPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->verifyNow();
    }
}
