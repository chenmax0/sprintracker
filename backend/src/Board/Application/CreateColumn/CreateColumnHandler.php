<?php

declare(strict_types=1);

namespace App\Board\Application\CreateColumn;

use App\Board\Application\Port\ColumnIdGeneratorInterface;
use App\Board\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Board\Domain\BoardColumn;
use App\Board\Domain\BoardColumnRepositoryInterface;
use App\Board\Domain\Exception\NotAProjectMemberException;
use App\Board\Domain\ProjectId;

final class CreateColumnHandler
{
    public function __construct(
        private CreateColumnValidator $validator,
        private BoardColumnRepositoryInterface $columns,
        private ColumnIdGeneratorInterface $ids,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(CreateColumnPayload $payload): BoardColumn
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $projectId = new ProjectId($payload->projectId);

        $column = BoardColumn::create(
            $this->ids->generate(),
            $projectId,
            $payload->name,
            $this->columns->nextPosition($projectId),
        );
        $this->columns->save($column);

        return $column;
    }
}
