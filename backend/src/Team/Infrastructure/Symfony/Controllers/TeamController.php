<?php

declare(strict_types=1);

namespace App\Team\Infrastructure\Symfony\Controllers;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Team\Application\AddTeamMember\AddTeamMemberHandler;
use App\Team\Application\AddTeamMember\AddTeamMemberPayload;
use App\Team\Application\CreateTeam\CreateTeamHandler;
use App\Team\Application\CreateTeam\CreateTeamPayload;
use App\Team\Domain\Exception\MemberAlreadyInTeamException;
use App\Team\Domain\Exception\MemberNotFoundException;
use App\Team\Domain\Exception\NotATeamMemberException;
use App\Team\Domain\Exception\TeamNotFoundException;
use App\Team\Domain\MemberId;
use App\Team\Domain\TeamId;
use App\Team\Domain\TeamMembershipRepositoryInterface;
use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TeamController
{
    public function create(CreateTeamHandler $handler, Request $request, #[CurrentUser] SecurityUser $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new CreateTeamPayload($data['name'] ?? '', $user->getId());

        try {
            $team = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
        }

        return new JsonResponse([
            'id' => (string) $team->getId(),
            'name' => $team->getName(),
        ], 201);
    }

    public function addMember(AddTeamMemberHandler $handler, Request $request, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $payload = new AddTeamMemberPayload($teamId, $user->getId(), $data['email'] ?? '');

        try {
            $membership = $handler->handle($payload);
        } catch (LazyAssertionException $e) {
            $errors = array_map(static fn ($error) => $error->getMessage(), $e->getErrorExceptions());

            return new JsonResponse(['errors' => $errors], 422);
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

    public function listMembers(TeamMembershipRepositoryInterface $memberships, #[CurrentUser] SecurityUser $user, string $teamId): JsonResponse
    {
        $teamIdVo = new TeamId($teamId);

        if (null === $memberships->findMembership($teamIdVo, new MemberId($user->getId()))) {
            return new JsonResponse(['errors' => ['Only members of a team can view its members.']], 403);
        }

        $members = array_map(
            static fn ($membership) => [
                'memberId' => (string) $membership->getMemberId(),
                'role' => $membership->getRole()->value,
            ],
            $memberships->findByTeamId($teamIdVo),
        );

        return new JsonResponse($members);
    }
}
