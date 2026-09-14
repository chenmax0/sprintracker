<?php

declare(strict_types=1);

namespace App\Ticket\Domain\Exception;

final class AssigneeNotAProjectMemberException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('A ticket can only be assigned to a member of the project\'s team.');
    }
}
