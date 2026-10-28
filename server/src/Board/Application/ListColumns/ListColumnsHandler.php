<?php

declare(strict_types=1);

namespace App\Board\Application\ListColumns;

use App\Board\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Board\Domain\BoardColumn;
use App\Board\Domain\BoardColumnRepositoryInterface;
use App\Board\Domain\Exception\NotAProjectMemberException;
use App\Board\Domain\ProjectId;

final class ListColumnsHandler
{
    public function __construct(
        private ListColumnsValidator $validator,
        private BoardColumnRepositoryInterface $columns,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    /**
     * @return list<BoardColumn>
     */
    public function handle(ListColumnsPayload $payload): array
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        return $this->columns->findByProjectId(new ProjectId($payload->projectId));
    }
}
