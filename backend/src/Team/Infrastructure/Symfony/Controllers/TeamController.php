<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Shared\Infrastructure\Symfony\JsonBody;
use App\Team\Application\AddTeamMember\AddTeamMemberHandler;
use App\Team\Application\AddTeamMember\AddTeamMemberPayload;
use App\Team\Application\CreateTeam\CreateTeamHandler;
use App\Team\Application\CreateTeam\CreateTeamPayload;
use App\Team\Application\GetTeam\GetTeamHandler;
use App\Team\Application\GetTeam\GetTeamPayload;
use App\Team\Application\ListMyTeams\ListMyTeamsHandler;
use App\Team\Application\ListMyTeams\ListMyTeamsPayload;
use App\Team\Application\ListTeamMembers\ListTeamMembersHandler;
use App\Team\Application\ListTeamMembers\ListTeamMembersPayload;
use App\Team\Domain\Exception\MemberAlreadyInTeamException;
use App\Team\Domain\Exception\MemberNotFoundException;
use App\Team\Domain\Exception\NotATeamMemberException;
use App\Team\Domain\Exception\TeamNotFoundException;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TeamController
{
    public function create(CreateTeamHandler $handler, Request $request, #[CurrentUser] SecurityUser $user): JsonResponse
    {
        $data = new JsonBody($request);

        $payload = new CreateTeamPayload($data->string('name'), $user->getId());

        try {
            $team = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        }

        return new JsonResponse([
            'id' => (string) $team->getId(),
            'name' => $team->getName(),
        ], 201);
    }

    public function list(ListMyTeamsHandler $handler, #[CurrentUser] SecurityUser $user): JsonResponse
    {
        $teams = $handler->handle(new ListMyTeamsPayload($user->getId()));

        $items = array_map(
            static fn ($team) => [
                'id' => (string) $team->getId(),
                'name' => $team->getName(),
            ],
            $teams,
        );

        return new JsonResponse($items);
    }

    public function show(GetTeamHandler $handler, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        try {
            $team = $handler->handle(new GetTeamPayload($teamId, $user->getId()));
        } catch (TeamNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotATeamMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        return new JsonResponse([
            'id' => (string) $team->getId(),
            'name' => $team->getName(),
        ]);
    }

    public function addMember(AddTeamMemberHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        $data = new JsonBody($request);

        $payload = new AddTeamMemberPayload($teamId, $user->getId(), $data->string('email'));

        try {
            $membership = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            return $this->validationErrorResponse($e);
        } catch (TeamNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 404);
        } catch (NotATeamMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        } catch (MemberAlreadyInTeamException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 409);
        } catch (MemberNotFoundException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 422);
        }

        return new JsonResponse([
            'teamId' => (string) $membership->getTeamId(),
            'memberId' => (string) $membership->getMemberId(),
            'role' => $membership->getRole()->value,
        ], 201);
    }

    public function listMembers(ListTeamMembersHandler $handler, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        try {
            $memberships = $handler->handle(new ListTeamMembersPayload($teamId, $user->getId()));
        } catch (NotATeamMemberException $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], 403);
        }

        $items = array_map(
            static fn ($membership) => [
                'memberId' => (string) $membership->getMemberId(),
                'role' => $membership->getRole()->value,
            ],
            $memberships,
        );

        return new JsonResponse($items);
    }

    private function validationErrorResponse(LazyAssertionException $e): JsonResponse
    {
        $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

        return new JsonResponse(['errors' => $errors], 422);
    }
}
