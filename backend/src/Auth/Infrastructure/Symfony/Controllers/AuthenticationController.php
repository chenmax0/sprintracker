<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Symfony\Controllers;

use App\Auth\Application\RegisterUser\RegisterUserCommand;
use App\Auth\Application\RegisterUser\RegisterUserHandler;
use App\Auth\Domain\Exception\EmailAlreadyUsedException;
use App\Auth\Domain\Exception\InvalidEmailException;
use App\Auth\Infrastructure\Security\SecurityUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthenticationController
{
    public function __construct(
        private RegisterUserHandler $registerUserHandler,
        private ValidatorInterface $validator,
    ) {
    }

    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $email = $data['email'] ?? '';
        $name = $data['name'] ?? '';
        $password = $data['password'] ?? '';

        $violations = $this->validator->validate($email, [new Assert\NotBlank(), new Assert\Email()]);
        $violations->addAll($this->validator->validate($name, [new Assert\NotBlank()]));
        $violations->addAll($this->validator->validate($password, [new Assert\NotBlank(), new Assert\Length(min: 8)]));

        if (count($violations) > 0) {
            $errors = array_map(static fn ($violation) => $violation->getMessage(), iterator_to_array($violations));

            return new JsonResponse(['errors' => $errors], 422);
        }

        try {
            $user = ($this->registerUserHandler)(new RegisterUserCommand($email, $name, $password));
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
