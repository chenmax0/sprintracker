<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class SprintNotInProjectException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This sprint does not belong to the ticket\'s project.');
    }
}
