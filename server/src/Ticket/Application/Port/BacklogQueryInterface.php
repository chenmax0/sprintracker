<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

use App\Ticket\Application\ListBacklog\BacklogPage;
use App\Ticket\Application\ListBacklog\ListBacklogPayload;

/**
 * Read model for the backlog page. A ticket is in the backlog when it belongs
 * to no sprint, or to a completed sprint without having reached the board's
 * rightmost column (it is waiting to be carried over). Filtering, sorting and
 * paging happen in SQL so the page stays fast however large the backlog grows;
 * it reads across Sprint/Board tables on purpose, like the other read models.
 */
interface BacklogQueryInterface
{
    public function find(ListBacklogPayload $payload): BacklogPage;
}
