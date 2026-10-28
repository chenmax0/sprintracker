<?php

declare(strict_types=1);

namespace App\Project\Domain\Exception;

final class NotTeamOwnerException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Only a team owner can create a project in this team.');
    }
}
