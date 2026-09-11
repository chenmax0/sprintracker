<?php

declare(strict_types=1);

namespace App\Team\Domain\Exception;

final class TeamNotFoundException extends \DomainException
{
    public function __construct(string $teamId)
    {
        parent::__construct(sprintf('Team "%s" not found.', $teamId));
    }
}
