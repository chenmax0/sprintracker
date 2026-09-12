<?php

namespace App\Tests\Sprint\Infrastructure\Symfony\Controllers;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateSprintActionTest extends WebTestCase
{
    use ResetsDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    private function registerAndLogin(string $email): string
    {
        $container = $this->client->getContainer();
        $users = $container->get(UserRepositoryInterface::class);
        $hasher = $container->get(PasswordHasherInterface::class);
        $ids = $container->get(UserIdGeneratorInterface::class);

        $users->save(User::register($ids->generate(), new Email($email), 'Name', $hasher->hash('password123')));

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => $email,
            'password' => 'password123',
        ]));

        return json_decode($this->client->getResponse()->getContent(), true)['token'];
    }

    private function createTeamAndProject(string $ownerToken): string
    {
        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Sprint Squad']));
        $teamId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Mini Jira']));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function addMember(string $ownerToken, string $teamId, string $email): void
    {
        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => $email]));
    }

    public function testAnyTeamMemberCanCreateSprint(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $memberToken = $this->registerAndLogin('bob@example.com');

        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Sprint Squad']));
        $teamId = json_decode($this->client->getResponse()->getContent(), true)['id'];
        $this->addMember($ownerToken, $teamId, 'bob@example.com');

        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Mini Jira']));
        $projectId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$memberToken,
        ], content: json_encode(['startDate' => '2026-09-15', 'endDate' => '2026-09-29']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Sprint 1', $data['name']);
    }

    public function testSprintNumberIncrementsPerProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['startDate' => '2026-09-15', 'endDate' => '2026-09-29']));
        self::assertSame('Sprint 1', json_decode($this->client->getResponse()->getContent(), true)['name']);

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['startDate' => '2026-09-29', 'endDate' => '2026-10-13']));
        self::assertSame('Sprint 2', json_decode($this->client->getResponse()->getContent(), true)['name']);
    }

    public function testEndDateBeforeStartDateIsRejected(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['startDate' => '2026-10-13', 'endDate' => '2026-09-29']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testInvalidDateFormatIsRejected(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['startDate' => 'not-a-date', 'endDate' => '2026-09-29']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotCreateSprint(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['startDate' => '2026-09-15', 'endDate' => '2026-09-29']));

        self::assertResponseStatusCodeSame(403);
    }
}
