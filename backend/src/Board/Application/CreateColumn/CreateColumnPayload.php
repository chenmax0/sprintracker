<?php

declare(strict_types=1);

namespace App\Board\Application\CreateColumn;

final class CreateColumnPayload
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
        public readonly string $name,
    ) {
    }
}
