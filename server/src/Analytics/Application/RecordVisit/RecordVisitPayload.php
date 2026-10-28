<?php

declare(strict_types=1);

namespace App\Analytics\Application\RecordVisit;

final class RecordVisitPayload
{
    public function __construct(
        public readonly string $visitorId,
        public readonly ?string $userId,
    ) {
    }
}
