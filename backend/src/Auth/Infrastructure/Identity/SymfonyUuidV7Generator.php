<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Identity;

use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\UserId;
use Symfony\Component\Uid\Uuid;

final class SymfonyUuidV7Generator implements UserIdGeneratorInterface
{
    public function generate(): UserId
    {
        return new UserId(Uuid::v7()->toRfc4122());
    }
}
