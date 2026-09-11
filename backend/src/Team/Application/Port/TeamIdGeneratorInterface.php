<?php

declare(strict_types=1);

namespace App\Team\Application\Port;

use App\Team\Domain\TeamId;

interface TeamIdGeneratorInterface
{
    public function generate(): TeamId;
}
