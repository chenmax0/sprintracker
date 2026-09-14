<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

enum TicketStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
}
