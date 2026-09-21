<?php

declare(strict_types=1);

namespace App\Project\Domain;

final class Project
{
    private function __construct(
        private ProjectId $id,
        private TeamId $teamId,
        private string $name,
    ) {
    }

    public static function create(ProjectId $id, TeamId $teamId, string $name): self
    {
        return new self($id, $teamId, $name);
    }

    public static function fromPersistence(ProjectId $id, TeamId $teamId, string $name): self
    {
        return new self($id, $teamId, $name);
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function getId(): ProjectId
    {
        return $this->id;
    }

    public function getTeamId(): TeamId
    {
        return $this->teamId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
