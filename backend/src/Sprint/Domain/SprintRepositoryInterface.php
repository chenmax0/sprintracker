<?php

declare(strict_types=1);

namespace App\Sprint\Domain;

interface SprintRepositoryInterface
{
    public function findById(SprintId $id): ?Sprint;

    public function save(Sprint $sprint): void;

    /**
     * The next sprint number for a project (1 for its first sprint).
     */
    public function nextSprintNumber(ProjectId $projectId): int;

    /**
     * @return list<Sprint>
     */
    public function findByProjectId(ProjectId $projectId): array;
}
