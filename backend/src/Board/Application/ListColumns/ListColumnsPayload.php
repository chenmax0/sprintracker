<?php

declare(strict_types=1);

namespace App\Board\Application\ListColumns;

final class ListColumnsPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
    ) {
    }
}
