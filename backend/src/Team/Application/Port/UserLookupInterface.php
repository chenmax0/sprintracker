<?php

declare(strict_types=1);

namespace App\Team\Application\Port;

/**
 * Anti-corruption layer towards the Auth context: Team never depends on
 * Auth's Domain classes, it only asks "is there a user id for this email?"
 * through this port, implemented in Infrastructure with a direct SQL query.
 */
interface UserLookupInterface
{
    public function findIdByEmail(string $email): ?string;
}
