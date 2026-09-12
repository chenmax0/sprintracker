<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Project\Application\CreateProject\CreateProjectHandler;
use App\Project\Application\CreateProject\CreateProjectPayload;
use App\Project\Domain\Exception\NotTeamOwnerException;
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
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        } catch (NotTeamOwnerException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse([
            'id' => (string) $project->getId(),
            'teamId' => (string) $project->getTeamId(),
            'name' => $project->getName(),
        ], 201);
    }
}
