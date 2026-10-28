<?php

declare(strict_types=1);

namespace App\Project\Application\ListMyProjects;

final class ListMyProjectsPayload
{
    public function __construct(
        public readonly string $requesterMemberId,
    ) {
    }
}
