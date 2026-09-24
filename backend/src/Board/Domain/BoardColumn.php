<?php

declare(strict_types=1);

namespace App\Board\Domain;

final class BoardColumn
{
    private function __construct(
        private ColumnId $id,
        private ProjectId $projectId,
        private string $name,
        private int $position,
    ) {
    }

    public static function create(ColumnId $id, ProjectId $projectId, string $name, int $position): self
    {
        return new self($id, $projectId, $name, $position);
    }

    public static function fromPersistence(ColumnId $id, ProjectId $projectId, string $name, int $position): self
    {
        return new self($id, $projectId, $name, $position);
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function moveTo(int $position): void
    {
        $this->position = $position;
    }

    public function getId(): ColumnId
    {
        return $this->id;
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
