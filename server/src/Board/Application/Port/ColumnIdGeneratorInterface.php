<?php

declare(strict_types=1);

namespace App\Board\Application\Port;

use App\Board\Domain\ColumnId;

interface ColumnIdGeneratorInterface
{
    public function generate(): ColumnId;
}
