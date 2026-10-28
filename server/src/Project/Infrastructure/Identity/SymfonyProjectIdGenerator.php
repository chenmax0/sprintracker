<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Identity;

use App\Project\Application\Port\ProjectIdGeneratorInterface;
use App\Project\Domain\ProjectId;
use Symfony\Component\Uid\Uuid;

final class SymfonyProjectIdGenerator implements ProjectIdGeneratorInterface
{
    public function generate(): ProjectId
    {
        return new ProjectId(Uuid::v7()->toRfc4122());
    }
}
