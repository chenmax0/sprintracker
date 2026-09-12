<?php

declare(strict_types=1);

namespace App\Sprint\Domain;

use App\Sprint\Domain\Exception\InvalidProjectIdException;

/**
 * Identifies a project from the Sprint context's point of view. Its value
 * happens to be a Project id (from the Project context), but this type
 * deliberately does not depend on App\Project\Domain\ProjectId - the two
 * bounded contexts stay decoupled, they just happen to agree on "a project
 * is identified by a UUID".
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
