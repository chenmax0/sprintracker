<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Identity;

use App\Ticket\Application\Port\CommentIdGeneratorInterface;
use App\Ticket\Domain\CommentId;
use Symfony\Component\Uid\Uuid;

final class SymfonyCommentIdGenerator implements CommentIdGeneratorInterface
{
    public function generate(): CommentId
    {
        return new CommentId(Uuid::v7()->toRfc4122());
    }
}
