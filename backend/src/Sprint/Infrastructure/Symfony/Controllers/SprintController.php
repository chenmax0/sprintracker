<?php

declare(strict_types=1);

namespace App\Sprint\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Sprint\Application\CreateSprint\CreateSprintHandler;
use App\Sprint\Application\CreateSprint\CreateSprintPayload;
use App\Sprint\Domain\Exception\InvalidSprintDatesException;
use App\Sprint\Domain\Exception\NotAProjectMemberException;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class SprintController
{
    public function create(CreateSprintHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new CreateSprintPayload(
            $projectId,
            $user->getId(),
            $data['startDate'] ?? '',
            $data['endDate'] ?? '',
        );

        try {
            $sprint = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (InvalidSprintDatesException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse([
            'id' => (string) $sprint->getId(),
            'projectId' => (string) $sprint->getProjectId(),
            'name' => $sprint->getName(),
            'startDate' => $sprint->getStartDate()->format('Y-m-d'),
            'endDate' => $sprint->getEndDate()->format('Y-m-d'),
        ], 201);
    }
}
