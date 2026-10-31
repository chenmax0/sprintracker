<?php

declare(strict_types=1);

namespace App\Ticket\Application\ListBacklog;

final class BacklogPage
{
    /**
     * @param list<BacklogTicketView> $items
     * @param int                     $total every ticket matching the filters, across all pages
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {
    }
}
