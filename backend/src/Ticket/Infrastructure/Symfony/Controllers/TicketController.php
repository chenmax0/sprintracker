<?php

declare(strict_types=1);

namespace App\Ticket\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Ticket\Application\AddComment\AddCommentHandler;
use App\Ticket\Application\AddComment\AddCommentPayload;
use App\Ticket\Application\AssignTicket\AssignTicketHandler;
use App\Ticket\Application\AssignTicket\AssignTicketPayload;
use App\Ticket\Application\CreateTicket\CreateTicketHandler;
use App\Ticket\Application\CreateTicket\CreateTicketPayload;
use App\Ticket\Domain\Comment;
use App\Ticket\Domain\CommentRepositoryInterface;
use App\Ticket\Domain\Exception\AssigneeNotAProjectMemberException;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\SprintNotInProjectException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\Ticket;
use App\Ticket\Domain\TicketId;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TicketController
{
    public function create(CreateTicketHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new CreateTicketPayload(
            $projectId,
            $user->getId(),
            $data['title'] ?? '',
            $data['description'] ?? null,
            $data['sprintId'] ?? null,
            $data['assigneeId'] ?? null,
        );

        try {
            $ticket = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (AssigneeNotAProjectMemberException|SprintNotInProjectException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse($this->serializeTicket($ticket), 201);
    }

    public function assign(AssignTicketHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $ticketId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new AssignTicketPayload($ticketId, $user->getId(), $data['assigneeId'] ?? null);

        try {
            $ticket = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (TicketNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (AssigneeNotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse($this->serializeTicket($ticket));
    }

    public function addComment(AddCommentHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $ticketId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new AddCommentPayload($ticketId, $user->getId(), $data['content'] ?? '');

        try {
            $comment = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (TicketNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse([
            'id' => (string) $comment->getId(),
            'ticketId' => (string) $comment->getTicketId(),
            'authorId' => (string) $comment->getAuthorId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], 201);
    }

    public function listComments(CommentRepositoryInterface $comments, string $ticketId): JsonResponse
    {
        $items = array_map(
            static fn (Comment $comment) => [
                'id' => (string) $comment->getId(),
                'authorId' => (string) $comment->getAuthorId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $comments->findByTicketId(new TicketId($ticketId)),
        );

        return new JsonResponse($items);
    }

    private function serializeTicket(Ticket $ticket): array
    {
        return [
            'id' => (string) $ticket->getId(),
            'projectId' => (string) $ticket->getProjectId(),
            'sprintId' => null !== $ticket->getSprintId() ? (string) $ticket->getSprintId() : null,
            'title' => $ticket->getTitle(),
            'description' => $ticket->getDescription(),
            'status' => $ticket->getStatus()->value,
            'reporterId' => (string) $ticket->getReporterId(),
            'assigneeId' => null !== $ticket->getAssigneeId() ? (string) $ticket->getAssigneeId() : null,
        ];
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
