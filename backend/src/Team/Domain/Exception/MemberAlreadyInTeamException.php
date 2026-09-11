<?php

declare(strict_types=1);

namespace App\Team\Domain\Exception;

final class MemberAlreadyInTeamException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This user is already a member of the team.');
    }
}
