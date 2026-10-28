<?php

declare(strict_types=1);

namespace App\Analytics\Application\Port;

interface VisitorActivityRecorderInterface
{
    /**
     * Marks the visitor as active during the given UTC hour. Idempotent: calling
     * it again for the same visitor and hour never adds a second row. A known
     * userId is kept even if a later call for the same hour doesn't carry one.
     */
    public function record(string $visitorId, ?string $userId, \DateTimeImmutable $hour): void;
}
