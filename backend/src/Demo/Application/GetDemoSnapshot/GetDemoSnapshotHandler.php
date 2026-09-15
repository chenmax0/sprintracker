<?php

declare(strict_types=1);

namespace App\Demo\Application\GetDemoSnapshot;

use App\Demo\Application\Port\DemoRepositoryInterface;

final class GetDemoSnapshotHandler
{
    public function __construct(private DemoRepositoryInterface $demo)
    {
    }

    /**
     * @return array{team: array, project: array, members: list<array>, sprints: list<array>, tickets: list<array>}
     */
    public function handle(GetDemoSnapshotPayload $payload): array
    {
        return $this->demo->getSnapshot();
    }
}
