<?php

declare(strict_types=1);

namespace App\Analytics\Domain;

enum Granularity: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';

    /**
     * Widest window (in days) that still yields a readable number of buckets.
     */
    public function maxSpanInDays(): int
    {
        return match ($this) {
            self::Hour => 31,
            self::Day, self::Week, self::Month => 731,
        };
    }
}
