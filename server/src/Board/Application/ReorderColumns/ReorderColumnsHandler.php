<?php

declare(strict_types=1);

namespace App\Board\Application\ReorderColumns;

use App\Board\Application\Port\ProjectTeamMembershipCheckerInterface;
use App\Board\Domain\BoardColumnRepositoryInterface;
use App\Board\Domain\Exception\InvalidColumnOrderException;
use App\Board\Domain\Exception\NotAProjectMemberException;
use App\Board\Domain\ProjectId;

final class ReorderColumnsHandler
{
    public function __construct(
        private ReorderColumnsValidator $validator,
        private BoardColumnRepositoryInterface $columns,
        private ProjectTeamMembershipCheckerInterface $projectTeamMembership,
    ) {
    }

    /**
     * @return list<\App\Board\Domain\BoardColumn>
     */
    public function handle(ReorderColumnsPayload $payload): array
    {
        $this->validator->validate($payload);

        if (!$this->projectTeamMembership->isMember($payload->projectId, $payload->requesterMemberId)) {
            throw new NotAProjectMemberException();
        }

        $projectId = new ProjectId($payload->projectId);
        $columns = $this->columns->findByProjectId($projectId);

        $currentIds = array_map(static fn ($column) => (string) $column->getId(), $columns);
        $sortedGivenIds = $payload->orderedColumnIds;
        sort($sortedGivenIds);
        $sortedCurrentIds = $currentIds;
        sort($sortedCurrentIds);

        if ($sortedGivenIds !== $sortedCurrentIds) {
            throw new InvalidColumnOrderException();
        }

        $columnsById = [];
        foreach ($columns as $column) {
            $columnsById[(string) $column->getId()] = $column;
        }

        foreach ($payload->orderedColumnIds as $position => $columnId) {
            $column = $columnsById[$columnId];
            $column->moveTo($position);
            $this->columns->save($column);
        }

        return $this->columns->findByProjectId($projectId);
    }
}
