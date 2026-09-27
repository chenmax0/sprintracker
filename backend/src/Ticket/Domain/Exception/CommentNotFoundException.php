<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class CommentNotFoundException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Comment not found.');
    }
}
