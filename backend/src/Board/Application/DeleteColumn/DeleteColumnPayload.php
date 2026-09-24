<?php

declare(strict_types=1);

namespace App\Board\Application\DeleteColumn;

final class DeleteColumnPayload
{
    public function __construct(
        public readonly string $columnId,
        public readonly string $requesterMemberId,
    ) {
    }
}
