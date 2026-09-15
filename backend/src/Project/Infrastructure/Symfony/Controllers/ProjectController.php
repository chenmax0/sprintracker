<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Project\Application\CreateProject\CreateProjectHandler;
use App\Project\Application\CreateProject\CreateProjectPayload;
use App\Project\Application\GetProject\GetProjectHandler;
use App\Project\Application\GetProject\GetProjectPayload;
use App\Project\Application\ListMyProjects\ListMyProjectsHandler;
use App\Project\Application\ListMyProjects\ListMyProjectsPayload;
use App\Project\Application\ListMyProjects\MyProjectView;
use App\Project\Application\ListProjects\ListProjectsHandler;
use App\Project\Application\ListProjects\ListProjectsPayload;
use App\Project\Domain\Exception\NotATeamMemberException;
use App\Project\Domain\Exception\NotTeamOwnerException;
use App\Project\Domain\Exception\ProjectNotFoundException;
use App\Project\Domain\Project;
use App\Shared\Infrastructure\Symfony\JsonBody;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ProjectController
{
    public function create(CreateProjectHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        $data = new JsonBody($request);

        $payload = new CreateProjectPayload($teamId, $user->getId(), $data->string('name'));

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

    public function listMine(ListMyProjectsHandler $handler, #[CurrentUser] SecurityUser $user): JsonResponse
    {
        $projects = $handler->handle(new ListMyProjectsPayload($user->getId()));

        return new JsonResponse(array_map($this->serializeMyProject(...), $projects));
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

    private function serializeMyProject(MyProjectView $project): array
    {
        return [
            'id' => $project->id,
            'teamId' => $project->teamId,
            'teamName' => $project->teamName,
            'name' => $project->name,
        ];
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
