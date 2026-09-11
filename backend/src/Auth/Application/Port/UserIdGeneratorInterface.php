<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

use App\Auth\Domain\UserId;

interface UserIdGeneratorInterface
{
    public function generate(): UserId;
}
