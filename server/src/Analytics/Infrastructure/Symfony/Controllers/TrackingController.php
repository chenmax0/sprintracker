<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Symfony\Controllers;

use App\Analytics\Application\RecordVisit\RecordVisitHandler;
use App\Analytics\Application\RecordVisit\RecordVisitPayload;
use App\Analytics\Infrastructure\Security\OptionalUserIdResolver;
use App\Shared\Infrastructure\Symfony\JsonBody;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class TrackingController
{
    public function track(RecordVisitHandler $handler, OptionalUserIdResolver $userIds, Request $request): JsonResponse
    {
        $data = new JsonBody($request);

        $payload = new RecordVisitPayload($data->string('visitorId'), $userIds->resolve($request));

        try {
            $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        }

        return new JsonResponse(null, 204);
    }
}
