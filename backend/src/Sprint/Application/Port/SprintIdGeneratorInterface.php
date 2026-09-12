<?php

declare(strict_types=1);

namespace App\Sprint\Application\Port;

use App\Sprint\Domain\SprintId;

interface SprintIdGeneratorInterface
{
    public function generate(): SprintId;
}
