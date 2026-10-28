<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Identity;

use App\Team\Application\Port\TeamIdGeneratorInterface;
use App\Team\Domain\TeamId;
use Symfony\Component\Uid\Uuid;

final class SymfonyTeamIdGenerator implements TeamIdGeneratorInterface
{
    public function generate(): TeamId
    {
        return new TeamId(Uuid::v7()->toRfc4122());
    }
}
