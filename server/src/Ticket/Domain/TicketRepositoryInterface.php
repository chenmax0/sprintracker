<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

interface TicketRepositoryInterface
{
    public function findById(TicketId $id): ?Ticket;

    public function save(Ticket $ticket): void;

    /**
     * @return list<Ticket>
     */
    public function findByProjectId(ProjectId $projectId): array;

    /**
     * The next ticket number for a project (1 for its first ticket).
     */
    public function nextTicketNumber(ProjectId $projectId): int;
}
