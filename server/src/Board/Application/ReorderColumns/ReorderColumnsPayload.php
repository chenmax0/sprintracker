<?php

declare(strict_types=1);

namespace App\Board\Application\ReorderColumns;

final class ReorderColumnsPayload
{
    /**
     * @param list<string> $orderedColumnIds
     */
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
        public readonly array $orderedColumnIds,
    ) {
    }
}
