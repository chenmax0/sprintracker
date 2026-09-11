<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Team\Application\CreateTeam\Handler as CreateTeamHandler;
use App\Team\Application\CreateTeam\Payload as CreateTeamPayload;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TeamController
{
    public function __construct(
        private CreateTeamHandler $createTeamHandler,
    ) {
    }

    public function create(Request $request, #[CurrentUser] SecurityUser $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new CreateTeamPayload($data['name'] ?? '', $user->getId());

        try {
            $team = $this->createTeamHandler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        }

        return new JsonResponse([
            'id' => (string) $team->getId(),
            'name' => $team->getName(),
        ], 201);
    }
}
