<?php

declare(strict_types=1);

namespace App\Team\Application\Port;

/**
 * Anti-corruption layer towards the Auth context: Team never depends on
 * Auth's Domain classes, it only asks for the display profile of a set of
 * user ids, implemented in Infrastructure with a direct SQL query.
 */
interface UserProfileLookupInterface
{
    /**
     * @param list<string> $userIds
     *
     * @return array<string, array{email: string, name: string}>
     */
    public function findByIds(array $userIds): array;
}
