<?php

declare(strict_types=1);

namespace App\Project\Application\ListMyProjects;

use App\Project\Application\Port\MyProjectsQueryInterface;

final class ListMyProjectsHandler
{
    public function __construct(
        private ListMyProjectsValidator $validator,
        private MyProjectsQueryInterface $myProjects,
    ) {
    }

    /**
     * @return list<MyProjectView>
     */
    public function handle(ListMyProjectsPayload $payload): array
    {
        $this->validator->validate($payload);

        return $this->myProjects->findForMember($payload->requesterMemberId);
    }
}
