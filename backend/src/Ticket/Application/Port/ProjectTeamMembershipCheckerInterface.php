<?php

declare(strict_types=1);

namespace App\Ticket\Application\Port;

/**
 * Anti-corruption layer towards Project and Team: Ticket never depends on
 * their Domain classes, it only asks "is this member part of the team that
 * owns this project?" through this port, implemented in Infrastructure with
 * a direct SQL query joining project and team_membership.
 */
interface ProjectTeamMembershipCheckerInterface
{
    public function isMember(string $projectId, string $memberId): bool;
}
