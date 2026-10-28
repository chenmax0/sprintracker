<?php

declare(strict_types=1);

namespace App\Project\Application\Port;

/**
 * Anti-corruption layer towards the Team context: Project never depends on
 * Team's Domain classes, it only asks "is this member an owner of this team?"
 * through this port, implemented in Infrastructure with a direct SQL query
 * on team_membership.
 */
interface TeamOwnershipCheckerInterface
{
    public function isOwner(string $teamId, string $memberId): bool;
}
