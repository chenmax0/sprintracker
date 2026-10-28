<?php

declare(strict_types=1);

namespace App\Board\Domain;

interface BoardColumnRepositoryInterface
{
    public function findById(ColumnId $id): ?BoardColumn;

    public function save(BoardColumn $column): void;

    public function delete(ColumnId $id): void;

    /**
     * @return list<BoardColumn> ordered by position, ascending
     */
    public function findByProjectId(ProjectId $projectId): array;

    public function nextPosition(ProjectId $projectId): int;
}
