<?php

declare(strict_types=1);

namespace App\Project\Domain\Exception;

final class ProjectNotFoundException extends \DomainException
{
    public function __construct(string $projectId)
    {
        parent::__construct(sprintf('Project "%s" not found.', $projectId));
    }
}
