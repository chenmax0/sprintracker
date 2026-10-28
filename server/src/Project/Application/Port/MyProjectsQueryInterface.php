<?php

declare(strict_types=1);

namespace App\Project\Application\Port;

use App\Project\Application\ListMyProjects\MyProjectView;

/**
 * Reads across the Project/Team boundary on purpose: listing "my projects"
 * needs each project's team name, which Project's own domain doesn't carry.
 * Implemented with a direct SQL join instead of depending on Team's Domain.
 */
interface MyProjectsQueryInterface
{
    /**
     * @return list<MyProjectView>
     */
    public function findForMember(string $memberId): array;
}
