<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

final class Ticket
{
    private function __construct(
        private TicketId $id,
        private ProjectId $projectId,
        private int $number,
        private ?SprintId $sprintId,
        private string $title,
        private ?string $description,
        private ColumnId $columnId,
        private MemberId $reporterId,
        private ?MemberId $assigneeId,
    ) {
    }

    public static function create(
        TicketId $id,
        ProjectId $projectId,
        int $number,
        ?SprintId $sprintId,
        string $title,
        ?string $description,
        ColumnId $columnId,
        MemberId $reporterId,
        ?MemberId $assigneeId,
    ): self {
        return new self($id, $projectId, $number, $sprintId, $title, $description, $columnId, $reporterId, $assigneeId);
    }

    public static function fromPersistence(
        TicketId $id,
        ProjectId $projectId,
        int $number,
        ?SprintId $sprintId,
        string $title,
        ?string $description,
        ColumnId $columnId,
        MemberId $reporterId,
        ?MemberId $assigneeId,
    ): self {
        return new self($id, $projectId, $number, $sprintId, $title, $description, $columnId, $reporterId, $assigneeId);
    }

    public function assignTo(?MemberId $assigneeId): void
    {
        $this->assigneeId = $assigneeId;
    }

    public function moveToSprint(?SprintId $sprintId): void
    {
        $this->sprintId = $sprintId;
    }

    public function moveToColumn(ColumnId $columnId): void
    {
        $this->columnId = $columnId;
    }

    public function getId(): TicketId
    {
        return $this->id;
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getSprintId(): ?SprintId
    {
        return $this->sprintId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getColumnId(): ColumnId
    {
        return $this->columnId;
    }

    public function getReporterId(): MemberId
    {
        return $this->reporterId;
    }

    public function getAssigneeId(): ?MemberId
    {
        return $this->assigneeId;
    }
}
