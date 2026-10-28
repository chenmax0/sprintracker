<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetVisitorStats;

final class VisitorStatsBucket
{
    public function __construct(
        /** Local start of the bucket, as Y-m-dTH:i:s (no offset: it is in the requested timezone). */
        public readonly string $start,
        public readonly int $visitors,
        public readonly int $users,
    ) {
    }
}
