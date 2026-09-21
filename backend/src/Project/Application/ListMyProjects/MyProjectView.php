<?php

declare(strict_types=1);

namespace App\Project\Application\ListMyProjects;

final class MyProjectView
{
    public function __construct(
        public readonly string $id,
        public readonly string $teamId,
        public readonly string $teamName,
        public readonly string $name,
        public readonly bool $isOwner,
    ) {
    }
}
