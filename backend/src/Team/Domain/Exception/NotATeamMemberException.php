<?php

declare(strict_types=1);

namespace App\Team\Domain\Exception;

final class NotATeamMemberException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Only members of a team can invite new members.');
    }
}
