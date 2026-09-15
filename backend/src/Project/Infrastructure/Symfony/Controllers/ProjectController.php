<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Project\Application\CreateProject\CreateProjectHandler;
use App\Project\Application\CreateProject\CreateProjectPayload;
use App\Project\Application\GetProject\GetProjectHandler;
use App\Project\Application\GetProject\GetProjectPayload;
use App\Project\Application\ListProjects\ListProjectsHandler;
use App\Project\Application\ListProjects\ListProjectsPayload;
use App\Project\Domain\Exception\NotATeamMemberException;
use App\Project\Domain\Exception\NotTeamOwnerException;
use App\Project\Domain\Exception\ProjectNotFoundException;
use App\Project\Domain\Project;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ProjectController
{
    public function create(CreateProjectHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new CreateProjectPayload($teamId, $user->getId(), $data['name'] ?? '');

        try {
            $project = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (NotTeamOwnerException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse($this->serializeProject($project), 201);
    }

    public function list(ListProjectsHandler $handler, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        try {
            $projects = $handler->handle(new ListProjectsPayload($teamId, $user->getId()));
        } catch (NotATeamMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse(array_map($this->serializeProject(...), $projects));
    }

    public function show(GetProjectHandler $handler, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        try {
            $project = $handler->handle(new GetProjectPayload($projectId, $user->getId()));
        } catch (ProjectNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotATeamMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse($this->serializeProject($project));
    }

    private function serializeProject(Project $project): array
    {
        return [
            'id' => (string) $project->getId(),
            'teamId' => (string) $project->getTeamId(),
            'name' => $project->getName(),
        ];
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
