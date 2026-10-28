<?php

declare(strict_types=1);

namespace App\Board\Application\RenameColumn;

final class RenameColumnPayload
{
    public function __construct(
        public readonly string $columnId,
        public readonly string $requesterMemberId,
        public readonly string $name,
    ) {
    }
}
