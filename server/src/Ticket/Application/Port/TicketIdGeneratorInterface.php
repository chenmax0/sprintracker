<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

use App\Ticket\Domain\TicketId;

interface TicketIdGeneratorInterface
{
    public function generate(): TicketId;
}
