<?php

declare(strict_types=1);

namespace App\Ticket\Domain;

use App\Ticket\Domain\Exception\InvalidProjectIdException;

/**
 * Identifies a project from the Ticket context's point of view - decoupled
 * from App\Project\Domain\ProjectId, same pattern as the other contexts.
 */
final class ProjectId
{
    private const FORMAT = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match(self::FORMAT, $value)) {
            throw new InvalidProjectIdException($value);
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
