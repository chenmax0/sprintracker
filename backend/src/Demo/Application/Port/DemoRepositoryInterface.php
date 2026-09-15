<?php

declare(strict_types=1);

namespace App\Demo\Application\Port;

interface DemoRepositoryInterface
{
    /**
     * @return array{team: array, project: array, sprints: list<array>, tickets: list<array>}
     */
    public function getSnapshot(): array;
}
