<?php

declare(strict_types=1);

namespace App\Team\Domain;

use App\Team\Domain\Exception\InvalidTeamIdException;

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
