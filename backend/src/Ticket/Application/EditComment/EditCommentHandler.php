<?php

declare(strict_types=1);

namespace App\Ticket\Application\EditComment;

use App\Ticket\Domain\Comment;
use App\Ticket\Domain\CommentId;
use App\Ticket\Domain\CommentRepositoryInterface;
use App\Ticket\Domain\Exception\CommentNotFoundException;
use App\Ticket\Domain\Exception\NotCommentAuthorException;
use App\Ticket\Domain\MemberId;

final class EditCommentHandler
{
    public function __construct(
        private EditCommentValidator $validator,
        private CommentRepositoryInterface $comments,
    ) {
    }

    public function handle(EditCommentPayload $payload): Comment
    {
        $this->validator->validate($payload);

        $comment = $this->comments->findById(new CommentId($payload->commentId));

        if (null === $comment) {
            throw new CommentNotFoundException();
        }

        if (!$comment->getAuthorId()->equals(new MemberId($payload->requesterMemberId))) {
            throw new NotCommentAuthorException();
        }

        $comment->edit($payload->content);
        $this->comments->save($comment);

        return $comment;
    }
}
