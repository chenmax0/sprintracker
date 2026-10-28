<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Symfony\Controllers;

use App\Analytics\Application\GetVisitorStats\GetVisitorStatsHandler;
use App\Analytics\Application\GetVisitorStats\GetVisitorStatsPayload;
use App\Analytics\Application\GetVisitorStats\VisitorStatsBucket;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin-only: /api/admin is restricted to ROLE_ADMIN in security.yaml.
 */
final class AdminAnalyticsController
{
    public function visitors(GetVisitorStatsHandler $handler, Request $request): JsonResponse
    {
        $query = $request->query;
        $granularity = $query->get('granularity', 'day');
        $timezone = $query->get('timezone', 'UTC');
        $from = $query->get('from');
        $to = $query->get('to');

        $payload = new GetVisitorStatsPayload(
            is_string($granularity) ? $granularity : '',
            is_string($timezone) ? $timezone : '',
            is_string($from) && '' !== $from ? $from : null,
            is_string($to) && '' !== $to ? $to : null,
        );

        try {
            $stats = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        }

        return new JsonResponse([
            'granularity' => $payload->granularity,
            'timezone' => $payload->timezone,
            'totals' => [
                'visitors' => $stats->totalVisitors,
                'users' => $stats->totalUsers,
            ],
            'series' => array_map(
                static fn (VisitorStatsBucket $bucket) => [
                    'start' => $bucket->start,
                    'visitors' => $bucket->visitors,
                    'users' => $bucket->users,
                ],
                $stats->series,
            ),
        ]);
    }
}
