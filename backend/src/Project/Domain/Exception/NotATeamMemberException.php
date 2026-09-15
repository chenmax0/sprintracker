<?php

declare(strict_types=1);

namespace App\Project\Domain\Exception;

final class NotATeamMemberException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Only members of the team can perform this action.');
    }
}
