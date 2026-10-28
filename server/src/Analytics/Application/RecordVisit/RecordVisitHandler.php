<?php

declare(strict_types=1);

namespace App\Analytics\Application\RecordVisit;

use App\Analytics\Application\Port\VisitorActivityRecorderInterface;

final class RecordVisitHandler
{
    public function __construct(
        private RecordVisitValidator $validator,
        private VisitorActivityRecorderInterface $recorder,
    ) {
    }

    public function handle(RecordVisitPayload $payload): void
    {
        $this->validator->validate($payload);

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $hour = $now->setTime((int) $now->format('G'), 0);

        $this->recorder->record(strtolower($payload->visitorId), $payload->userId, $hour);
    }
}
