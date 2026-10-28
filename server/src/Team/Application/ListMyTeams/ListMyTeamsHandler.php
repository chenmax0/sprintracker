<?php

declare(strict_types=1);

namespace App\Team\Application\ListMyTeams;

use App\Team\Domain\MemberId;
use App\Team\Domain\Team;
use App\Team\Domain\TeamRepositoryInterface;

final class ListMyTeamsHandler
{
    public function __construct(
        private ListMyTeamsValidator $validator,
        private TeamRepositoryInterface $teams,
    ) {
    }

    /**
     * @return list<Team>
     */
    public function handle(ListMyTeamsPayload $payload): array
    {
        $this->validator->validate($payload);

        return $this->teams->findByMemberId(new MemberId($payload->requesterMemberId));
    }
}
