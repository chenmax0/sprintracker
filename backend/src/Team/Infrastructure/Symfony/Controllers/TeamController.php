<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Team\Application\CreateTeam\CreateTeamCommand;
use App\Team\Application\CreateTeam\CreateTeamHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TeamController
{
    public function __construct(
        private CreateTeamHandler $createTeamHandler,
        private ValidatorInterface $validator,
    ) {
    }

    public function create(Request $request, #[CurrentUser] SecurityUser $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $name = $data['name'] ?? '';

        $violations = $this->validator->validate($name, [new Assert\NotBlank()]);

        if (count($violations) > 0) {
            $errors = array_map(static fn ($violation) => $violation->getMessage(), iterator_to_array($violations));

            return new JsonResponse(['errors' => $errors], 422);
        }

        $team = ($this->createTeamHandler)(new CreateTeamCommand($name, $user->getId()));

        return new JsonResponse([
            'id' => (string) $team->getId(),
            'name' => $team->getName(),
        ], 201);
    }
}
