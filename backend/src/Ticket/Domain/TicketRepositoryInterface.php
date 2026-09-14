<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

interface TicketRepositoryInterface
{
    public function findById(TicketId $id): ?Ticket;

    public function save(Ticket $ticket): void;
}
