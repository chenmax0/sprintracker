<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListBacklog;

enum BacklogSort: string
{
    case Oldest = 'oldest';
    case Newest = 'newest';
    case MostCarriedOver = 'most_carried_over';
}
