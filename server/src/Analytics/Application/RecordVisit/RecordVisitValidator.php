<?php

declare(strict_types=1);

namespace App\Analytics\Application\RecordVisit;

use Assert\Assert;

final class RecordVisitValidator
{
    public function validate(RecordVisitPayload $payload): void
    {
        Assert::lazy()
            ->that($payload->visitorId, 'visitorId')->uuid()
            ->verifyNow();
    }
}
