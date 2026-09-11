<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Symfony\Controllers;

use App\Auth\Application\RegisterUser\RegisterUserHandler;
use App\Auth\Application\RegisterUser\RegisterUserPayload;
use App\Auth\Domain\Exception\EmailAlreadyUsedException;
use App\Auth\Domain\Exception\InvalidEmailException;
use App\Auth\Infrastructure\Security\SecurityUser;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthenticationController
{
    public function register(RegisterUserHandler $handler, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new RegisterUserPayload(
            $data['email'] ?? '',
            $data['name'] ?? '',
            $data['password'] ?? '',
        );

        try {
            $user = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        } catch (InvalidEmailException|EmailAlreadyUsedException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse([
            'id' => (string) $user->getId(),
            'email' => (string) $user->getEmail(),
            'name' => $user->getName(),
        ], 201);
    }

    /**
     * Never actually reached: the "login" firewall's json_login authenticator
     * intercepts POST /api/login before routing dispatches to a controller.
     * Declared here only so the route has a documented, named action.
     */
    public function login(): never
    {
        throw new \LogicException('This action is handled by the "login" firewall (json_login), not by this controller.');
    }

    public function me(#[CurrentUser] SecurityUser $user): JsonResponse
    {
        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'name' => $user->getName(),
        ]);
    }
}
