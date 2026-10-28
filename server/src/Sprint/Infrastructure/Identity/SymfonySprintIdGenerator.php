<?php

declare(strict_types=1);

namespace App\Sprint\Infrastructure\Identity;

use App\Sprint\Application\Port\SprintIdGeneratorInterface;
use App\Sprint\Domain\SprintId;
use Symfony\Component\Uid\Uuid;

final class SymfonySprintIdGenerator implements SprintIdGeneratorInterface
{
    public function generate(): SprintId
    {
        return new SprintId(Uuid::v7()->toRfc4122());
    }
}
