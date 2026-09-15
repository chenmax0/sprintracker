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
use App\Ticket\Application\GetTicket\GetTicketHandler;
use App\Ticket\Application\GetTicket\GetTicketPayload;
use App\Ticket\Application\ListComments\ListCommentsHandler;
use App\Ticket\Application\ListComments\ListCommentsPayload;
use App\Ticket\Application\ListTickets\ListTicketsHandler;
use App\Ticket\Application\ListTickets\ListTicketsPayload;
use App\Ticket\Application\UpdateTicketStatus\UpdateTicketStatusHandler;
use App\Ticket\Application\UpdateTicketStatus\UpdateTicketStatusPayload;
use App\Ticket\Domain\Comment;
use App\Ticket\Domain\Exception\AssigneeNotAProjectMemberException;
use App\Ticket\Domain\Exception\NotAProjectMemberException;
use App\Ticket\Domain\Exception\SprintNotInProjectException;
use App\Ticket\Domain\Exception\TicketNotFoundException;
use App\Ticket\Domain\Ticket;
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

    public function list(ListTicketsHandler $handler, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        try {
            $tickets = $handler->handle(new ListTicketsPayload($projectId, $user->getId()));
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse(array_map($this->serializeTicket(...), $tickets));
    }

    public function show(GetTicketHandler $handler, #[CurrentUser] SecurityUser $user, string $ticketId): JsonResponse
    {
        try {
            $ticket = $handler->handle(new GetTicketPayload($ticketId, $user->getId()));
        } catch (TicketNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse($this->serializeTicket($ticket));
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

    public function updateStatus(UpdateTicketStatusHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $ticketId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new UpdateTicketStatusPayload($ticketId, $user->getId(), $data['status'] ?? '');

        try {
            $ticket = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (TicketNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
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

        return new JsonResponse($this->serializeComment($comment), 201);
    }

    public function listComments(ListCommentsHandler $handler, #[CurrentUser] SecurityUser $user, string $ticketId): JsonResponse
    {
        try {
            $comments = $handler->handle(new ListCommentsPayload($ticketId, $user->getId()));
        } catch (TicketNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse(array_map($this->serializeComment(...), $comments));
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

    private function serializeComment(Comment $comment): array
    {
        return [
            'id' => (string) $comment->getId(),
            'ticketId' => (string) $comment->getTicketId(),
            'authorId' => (string) $comment->getAuthorId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
