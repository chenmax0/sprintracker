<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class NotCommentAuthorException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Only the comment\'s author can edit it.');
    }
}
