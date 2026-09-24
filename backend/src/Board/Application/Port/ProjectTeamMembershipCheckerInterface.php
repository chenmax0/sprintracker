<?php

declare(strict_types=1);

namespace App\Board\Application\Port;

/**
 * Anti-corruption layer towards the Team context: Board never depends on
 * Team's Domain classes, it only asks "is this member part of this
 * project's team?" through this port, implemented in Infrastructure with a
 * direct SQL query joining project and team_membership.
 */
interface ProjectTeamMembershipCheckerInterface
{
    public function isMember(string $projectId, string $memberId): bool;
}
