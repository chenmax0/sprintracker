<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

final class Ticket
{
    private function __construct(
        private TicketId $id,
        private ProjectId $projectId,
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
        ?SprintId $sprintId,
        string $title,
        ?string $description,
        MemberId $reporterId,
        ?MemberId $assigneeId,
    ): self {
        return new self($id, $projectId, $sprintId, $title, $description, TicketStatus::Todo, $reporterId, $assigneeId);
    }

    /**
     * Reconstitutes a Ticket from persisted data. Only the persistence layer should call this.
     */
    public static function fromPersistence(
        TicketId $id,
        ProjectId $projectId,
        ?SprintId $sprintId,
        string $title,
        ?string $description,
        TicketStatus $status,
        MemberId $reporterId,
        ?MemberId $assigneeId,
    ): self {
        return new self($id, $projectId, $sprintId, $title, $description, $status, $reporterId, $assigneeId);
    }

    public function assignTo(?MemberId $assigneeId): void
    {
        $this->assigneeId = $assigneeId;
    }

    public function getId(): TicketId
    {
        return $this->id;
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
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
