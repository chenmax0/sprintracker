<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

use App\Ticket\Domain\CommentId;

interface CommentIdGeneratorInterface
{
    public function generate(): CommentId;
}
