<?php

declare(strict_types=1);

namespace App\Board\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Board\Application\CreateColumn\CreateColumnHandler;
use App\Board\Application\CreateColumn\CreateColumnPayload;
use App\Board\Application\DeleteColumn\DeleteColumnHandler;
use App\Board\Application\DeleteColumn\DeleteColumnPayload;
use App\Board\Application\ListColumns\ListColumnsHandler;
use App\Board\Application\ListColumns\ListColumnsPayload;
use App\Board\Application\RenameColumn\RenameColumnHandler;
use App\Board\Application\RenameColumn\RenameColumnPayload;
use App\Board\Application\ReorderColumns\ReorderColumnsHandler;
use App\Board\Application\ReorderColumns\ReorderColumnsPayload;
use App\Board\Domain\BoardColumn;
use App\Board\Domain\Exception\ColumnNotEmptyException;
use App\Board\Domain\Exception\ColumnNotFoundException;
use App\Board\Domain\Exception\InvalidColumnOrderException;
use App\Board\Domain\Exception\LastColumnException;
use App\Board\Domain\Exception\NotAProjectMemberException;
use App\Shared\Infrastructure\Symfony\JsonBody;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class BoardColumnController
{
    public function create(CreateColumnHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        $data = new JsonBody($request);

        try {
            $column = $handler->handle(new CreateColumnPayload($projectId, $user->getId(), $data->string('name')));
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse($this->serializeColumn($column), 201);
    }

    public function list(ListColumnsHandler $handler, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        try {
            $columns = $handler->handle(new ListColumnsPayload($projectId, $user->getId()));
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse(array_map($this->serializeColumn(...), $columns));
    }

    public function rename(RenameColumnHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $columnId): JsonResponse
    {
        $data = new JsonBody($request);

        try {
            $column = $handler->handle(new RenameColumnPayload($columnId, $user->getId(), $data->string('name')));
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (ColumnNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse($this->serializeColumn($column));
    }

    public function reorder(ReorderColumnsHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        $data = new JsonBody($request);

        try {
            $columns = $handler->handle(new ReorderColumnsPayload($projectId, $user->getId(), $data->stringArray('columnIds')));
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (InvalidColumnOrderException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse(array_map($this->serializeColumn(...), $columns));
    }

    public function delete(DeleteColumnHandler $handler, #[CurrentUser] SecurityUser $user, string $columnId): JsonResponse
    {
        try {
            $handler->handle(new DeleteColumnPayload($columnId, $user->getId()));
        } catch (ColumnNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (ColumnNotEmptyException|LastColumnException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 409);
        }

        return new JsonResponse(null, 204);
    }

    private function serializeColumn(BoardColumn $column): array
    {
        return [
            'id' => (string) $column->getId(),
            'projectId' => (string) $column->getProjectId(),
            'name' => $column->getName(),
            'position' => $column->getPosition(),
        ];
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
