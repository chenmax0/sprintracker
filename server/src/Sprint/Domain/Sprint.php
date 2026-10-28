<?php

declare(strict_types=1);

namespace App\Sprint\Domain;

use App\Sprint\Domain\Exception\InvalidSprintDatesException;
use App\Sprint\Domain\Exception\SprintAlreadyCompletedException;

final class Sprint
{
    private function __construct(
        private SprintId $id,
        private ProjectId $projectId,
        private int $number,
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private SprintStatus $status,
    ) {
        if ($this->endDate <= $this->startDate) {
            throw new InvalidSprintDatesException('Sprint end date must be after its start date.');
        }
    }

    public static function launch(
        SprintId $id,
        ProjectId $projectId,
        int $number,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): self {
        return new self($id, $projectId, $number, $startDate, $endDate, SprintStatus::Active);
    }

    public static function fromPersistence(
        SprintId $id,
        ProjectId $projectId,
        int $number,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        SprintStatus $status,
    ): self {
        return new self($id, $projectId, $number, $startDate, $endDate, $status);
    }

    public function complete(): void
    {
        if (SprintStatus::Completed === $this->status) {
            throw new SprintAlreadyCompletedException();
        }

        $this->status = SprintStatus::Completed;
    }

    public function getId(): SprintId
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

    public function getName(): string
    {
        return sprintf('Sprint %d', $this->number);
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getStatus(): SprintStatus
    {
        return $this->status;
    }
}
