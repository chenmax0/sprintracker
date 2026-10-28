<?php

declare(strict_types=1);

namespace App\Project\Domain;

use App\Project\Domain\Exception\InvalidTeamIdException;

/**
 * Identifies a team from the Project context's point of view. Its value happens
 * to be a Team id (from the Team context), but this type deliberately does not
 * depend on App\Team\Domain\TeamId - the two bounded contexts stay decoupled,
 * they just happen to agree on "a team is identified by a UUID".
 */
final class TeamId
{
    private const FORMAT = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match(self::FORMAT, $value)) {
            throw new InvalidTeamIdException($value);
        }

        $this->value = strtolower($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
