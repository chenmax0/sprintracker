<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Symfony\Controllers;

use App\Demo\Application\GetDemoSnapshot\GetDemoSnapshotHandler;
use App\Demo\Application\GetDemoSnapshot\GetDemoSnapshotPayload;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DemoController
{
    public function show(GetDemoSnapshotHandler $handler): JsonResponse
    {
        return new JsonResponse($handler->handle(new GetDemoSnapshotPayload()));
    }
}
