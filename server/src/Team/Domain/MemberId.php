<?php

declare(strict_types=1);

namespace App\Team\Domain;

use App\Team\Domain\Exception\InvalidMemberIdException;

/**
 * Identifies a member from the Team context's point of view. Its value happens
 * to be a User id (from the Auth context), but this type deliberately does not
 * depend on App\Auth\Domain\UserId - the two bounded contexts stay decoupled,
 * they just happen to agree on "a user is identified by a UUID".
 */
final class MemberId
{
    private const FORMAT = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match(self::FORMAT, $value)) {
            throw new InvalidMemberIdException($value);
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
