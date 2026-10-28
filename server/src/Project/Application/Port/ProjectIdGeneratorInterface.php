<?php

declare(strict_types=1);

namespace App\Project\Application\Port;

use App\Project\Domain\ProjectId;

interface ProjectIdGeneratorInterface
{
    public function generate(): ProjectId;
}
