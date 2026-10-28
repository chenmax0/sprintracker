<?php

declare(strict_types=1);

namespace App\Board\Application\RenameColumn;

use App\Board\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Board\Domain\BoardColumn;
use App\Board\Domain\BoardColumnRepositoryInterface;
use App\Board\Domain\ColumnId;
use App\Board\Domain\Exception\ColumnNotFoundException;
use App\Board\Domain\Exception\NotAProjectMemberException;

final class RenameColumnHandler
{
    public function __construct(
        private RenameColumnValidator $validator,
        private BoardColumnRepositoryInterface $columns,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    public function handle(RenameColumnPayload $payload): BoardColumn
    {
        $this->validator->validate($payload);

        $column = $this->columns->findById(new ColumnId($payload->columnId));

        if (null === $column) {
            throw new ColumnNotFoundException();
        }

        if (!$this->projectTeamMembership->isMember((string) $column->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $column->rename($payload->name);
        $this->columns->save($column);

        return $column;
    }
}
