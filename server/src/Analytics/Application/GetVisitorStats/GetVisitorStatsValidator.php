<?php

declare(strict_types=1);

namespace App\Analytics\Application\GetVisitorStats;

use App\Analytics\Domain\Granularity;
use Assert\Assert;

final class GetVisitorStatsValidator
{
    public function validate(GetVisitorStatsPayload $payload): void
    {
        $granularity = Granularity::tryFrom($payload->granularity);

        $lazy = Assert::lazy()
            ->that($payload->granularity, 'granularity')->satisfy(
                static fn () => null !== $granularity,
                'granularity must be one of: hour, day, week, month.',
            )
            ->that($payload->timezone, 'timezone')->inArray(\DateTimeZone::listIdentifiers());

        foreach (['from' => $payload->from, 'to' => $payload->to] as $field => $value) {
            if (null !== $value) {
                $lazy->that($value, $field)->date('Y-m-d');
            }
        }

        $lazy->verifyNow();

        if (null === $payload->from || null === $payload->to) {
            return;
        }

        $from = new \DateTimeImmutable($payload->from);
        $to = new \DateTimeImmutable($payload->to);
        $spanInDays = (int) $from->diff($to)->format('%r%a') + 1;

        Assert::lazy()
            ->that($spanInDays, 'to')->min(1, 'to must not be before from.')
            ->that($spanInDays, 'to')->max(
                $granularity->maxSpanInDays(),
                sprintf('The window is too wide for "%s" granularity (max %d days).', $granularity->value, $granularity->maxSpanInDays()),
            )
            ->verifyNow();
    }
}
