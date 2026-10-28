<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class NotAProjectMemberException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Only members of the project\'s team can perform this action.');
    }
}
