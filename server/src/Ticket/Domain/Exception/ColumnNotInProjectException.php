<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class ColumnNotInProjectException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This column does not belong to the ticket\'s project.');
    }
}
