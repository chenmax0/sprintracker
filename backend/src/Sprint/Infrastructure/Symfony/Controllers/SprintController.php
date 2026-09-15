<?php

declare(strict_types=1);

namespace App\Sprint\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Shared\Infrastructure\Symfony\JsonBody;
use App\Sprint\Application\CompleteSprint\CompleteSprintHandler;
use App\Sprint\Application\CompleteSprint\CompleteSprintPayload;
use App\Sprint\Application\LaunchSprint\LaunchSprintHandler;
use App\Sprint\Application\LaunchSprint\LaunchSprintPayload;
use App\Sprint\Application\ListSprints\ListSprintsHandler;
use App\Sprint\Application\ListSprints\ListSprintsPayload;
use App\Sprint\Domain\Exception\InvalidSprintDatesException;
use App\Sprint\Domain\Exception\NotAProjectMemberException;
use App\Sprint\Domain\Exception\SprintAlreadyActiveException;
use App\Sprint\Domain\Exception\SprintAlreadyCompletedException;
use App\Sprint\Domain\Exception\SprintNotFoundException;
use App\Sprint\Domain\Sprint;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class SprintController
{
    public function launch(LaunchSprintHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        $data = new JsonBody($request);

        $payload = new LaunchSprintPayload(
            $projectId,
            $user->getId(),
            $data->string('startDate'),
            $data->string('endDate'),
        );

        try {
            $sprint = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (InvalidSprintDatesException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        } catch (SprintAlreadyActiveException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 409);
        }

        return new JsonResponse($this->serializeSprint($sprint), 201);
    }

    public function complete(CompleteSprintHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $sprintId): JsonResponse
    {
        $data = new JsonBody($request);

        $payload = new CompleteSprintPayload(
            $sprintId,
            $user->getId(),
            $data->string('nextStartDate'),
            $data->string('nextEndDate'),
        );

        try {
            $nextSprint = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (SprintNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (SprintAlreadyCompletedException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 409);
        } catch (InvalidSprintDatesException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse($this->serializeSprint($nextSprint), 201);
    }

    public function list(ListSprintsHandler $handler, #[CurrentUser] SecurityUser $user, string $projectId): JsonResponse
    {
        try {
            $sprints = $handler->handle(new ListSprintsPayload($projectId, $user->getId()));
        } catch (NotAProjectMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse(array_map($this->serializeSprint(...), $sprints));
    }

    private function serializeSprint(Sprint $sprint): array
    {
        return [
            'id' => (string) $sprint->getId(),
            'projectId' => (string) $sprint->getProjectId(),
            'name' => $sprint->getName(),
            'startDate' => $sprint->getStartDate()->format('Y-m-d'),
            'endDate' => $sprint->getEndDate()->format('Y-m-d'),
            'status' => $sprint->getStatus()->value,
        ];
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
