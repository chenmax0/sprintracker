<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Identity;

use App\Ticket\Application\Port\TicketIdGeneratorInterface;
use App\Ticket\Domain\TicketId;
use Symfony\Component\Uid\Uuid;

final class SymfonyTicketIdGenerator implements TicketIdGeneratorInterface
{
    public function generate(): TicketId
    {
        return new TicketId(Uuid::v7()->toRfc4122());
    }
}
