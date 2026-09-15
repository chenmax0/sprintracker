<?php

declare(strict_types=1);

namespace App\Project\Domain;

interface ProjectRepositoryInterface
{
    public function findById(ProjectId $id): ?Project;

    public function save(Project $project): void;

    /**
     * @return list<Project>
     */
    public function findByTeamId(TeamId $teamId): array;
}
