<?php

declare(strict_types=1);

namespace App\Board\Infrastructure\Identity;

use App\Board\Application\Port\ColumnIdGeneratorInterface;
use App\Board\Domain\ColumnId;
use Symfony\Component\Uid\Uuid;

final class SymfonyColumnIdGenerator implements ColumnIdGeneratorInterface
{
    public function generate(): ColumnId
    {
        return new ColumnId(Uuid::v7()->toRfc4122());
    }
}
