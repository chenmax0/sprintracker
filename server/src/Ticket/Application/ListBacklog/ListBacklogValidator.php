<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListBacklog;

use Assert\Assert;

final class ListBacklogValidator
{
    public const MAX_PER_PAGE = 100;

    public function validate(ListBacklogPayload $payload): void
    {
        $lazy = Assert::lazy()
            ->that($payload->projectId, 'projectId')->notBlank()
            ->that($payload->requesterMemberId, 'requesterMemberId')->notBlank()
            ->that($payload->sort, 'sort')->inArray(array_column(BacklogSort::cases(), 'value'))
            ->that($payload->page, 'page')->min(1)
            ->that($payload->perPage, 'perPage')->between(1, self::MAX_PER_PAGE);

        if (null !== $payload->assignee && 'none' !== $payload->assignee) {
            $lazy->that($payload->assignee, 'assignee')->uuid();
        }

        $lazy->verifyNow();
    }
}
