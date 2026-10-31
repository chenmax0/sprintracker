<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListBacklog;

final class ListBacklogPayload
{
    /**
     * @param ?string $assignee a member id, or "none" for unassigned tickets
     */
    public function __construct(
        public readonly string $projectId,
        public readonly string $requesterMemberId,
        public readonly ?string $search = null,
        public readonly ?string $assignee = null,
        public readonly string $sort = BacklogSort::Oldest->value,
        public readonly int $page = 1,
        public readonly int $perPage = 25,
    ) {
    }
}
