<?php

declare(strict_types=1);

namespace App\Team\Domain\Exception;

final class InvalidTeamIdException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct(sprintf('"%s" is not a valid team id.', $value));
    }
}
