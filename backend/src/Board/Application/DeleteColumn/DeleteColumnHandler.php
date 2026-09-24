<?php

declare(strict_types=1);

namespace App\Board\Application\DeleteColumn;

use App\Board\Application\Port\ColumnTicketCheckerInterface;
use App\Board\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Board\Domain\BoardColumnRepositoryInterface;
use App\Board\Domain\ColumnId;
use App\Board\Domain\Exception\ColumnNotEmptyException;
use App\Board\Domain\Exception\ColumnNotFoundException;
use App\Board\Domain\Exception\LastColumnException;
use App\Board\Domain\Exception\NotAProjectMemberException;

final class DeleteColumnHandler
{
    public function __construct(
        private DeleteColumnValidator $validator,
        private BoardColumnRepositoryInterface $columns,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
        private ColumnTicketCheckerInterface $columnTickets,
    ) {
    }

    public function handle(DeleteColumnPayload $payload): void
    {
        $this->validator->validate($payload);

        $column = $this->columns->findById(new ColumnId($payload->columnId));

        if (null === $column) {
            throw new ColumnNotFoundException();
        }

        if (!$this->projectTeamMembership->isMember((string) $column->getProjectId(), $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        if ($this->columnTickets->hasTickets($payload->columnId)) {
            throw new ColumnNotEmptyException();
        }

        if (1 === count($this->columns->findByProjectId($column->getProjectId()))) {
            throw new LastColumnException();
        }

        $this->columns->delete($column->getId());
    }
}
