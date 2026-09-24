<?php

namespace App\Tests;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;

trait AppTestHelpers
{
    private function registerAndLogin(string $email): string
    {
        return $this->registerAndLoginWithId($email)[0];
    }

    /**
     * @return array{0: string, 1: string} [token, userId]
     */
    private function registerAndLoginWithId(string $email): array
    {
        $container = $this->client->getContainer();
        $users = $container->get(UserRepositoryInterface::class);
        $hasher = $container->get(PasswordHasherInterface::class);
        $ids = $container->get(UserIdGeneratorInterface::class);

        $user = User::register($ids->generate(), new Email($email), 'Name', $hasher->hash('password123'));
        $users->save($user);

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => $email,
            'password' => 'password123',
        ]));

        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];

        return [$token, (string) $user->getId()];
    }

    private function createTeam(string $ownerToken): string
    {
        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Sprint Squad']));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function addMember(string $ownerToken, string $teamId, string $email): void
    {
        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => $email]));
    }

    private function createProject(string $ownerToken, string $teamId): string
    {
        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Mini Jira']));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function createSprint(string $ownerToken, string $projectId): string
    {
        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['startDate' => '2026-09-15', 'endDate' => '2026-09-29']));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    /**
     * Completes the given sprint and launches the next one, returning its id.
     */
    private function completeSprint(string $ownerToken, string $sprintId): string
    {
        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['nextStartDate' => '2026-09-29', 'nextEndDate' => '2026-10-13']));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function createTicket(string $ownerToken, string $projectId, string $title = 'Fix bug'): string
    {
        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => $title]));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function createTeamAndProject(string $ownerToken): string
    {
        $teamId = $this->createTeam($ownerToken);

        return $this->createProject($ownerToken, $teamId);
    }

    /**
     * @return list<array{id: string, name: string, position: int}>
     */
    private function listColumns(string $ownerToken, string $projectId): array
    {
        $this->client->request('GET', "/api/projects/$projectId/columns", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    private function lastColumnId(string $ownerToken, string $projectId): string
    {
        $columns = $this->listColumns($ownerToken, $projectId);

        return end($columns)['id'];
    }
}
