<?php

declare(strict_types=1);

namespace App\Team\Application\ListMyTeams;

final class ListMyTeamsPayload
{
    public function __construct(
        public readonly string $requesterMemberId,
    ) {
    }
}
