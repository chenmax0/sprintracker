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
        private TicketStatus $status,
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
        MemberId $reporterId,
        ?MemberId $assigneeId,
    ): self {
        return new self($id, $projectId, $number, $sprintId, $title, $description, TicketStatus::Todo, $reporterId, $assigneeId);
    }

    public static function fromPersistence(
        TicketId $id,
        ProjectId $projectId,
        int $number,
        ?SprintId $sprintId,
        string $title,
        ?string $description,
        TicketStatus $status,
        MemberId $reporterId,
        ?MemberId $assigneeId,
    ): self {
        return new self($id, $projectId, $number, $sprintId, $title, $description, $status, $reporterId, $assigneeId);
    }

    public function assignTo(?MemberId $assigneeId): void
    {
        $this->assigneeId = $assigneeId;
    }

    public function moveToSprint(?SprintId $sprintId): void
    {
        $this->sprintId = $sprintId;
    }

    public function changeStatus(TicketStatus $status): void
    {
        $this->status = $status;
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

    public function getStatus(): TicketStatus
    {
        return $this->status;
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
