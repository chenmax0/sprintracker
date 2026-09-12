<?php

namespace App\Tests\Project\Infrastructure\Symfony\Controllers;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateProjectActionTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $connection = $this->client->getContainer()->get(Connection::class);
        $connection->executeStatement('DELETE FROM project');
        $connection->executeStatement('DELETE FROM team_membership');
        $connection->executeStatement('DELETE FROM team');
        $connection->executeStatement('DELETE FROM "user"');
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

    private function createTeam(string $ownerToken): string
    {
        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Sprint Squad']));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    public function testOwnerCanCreateProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Mini Jira']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Mini Jira', $data['name']);
        self::assertSame($teamId, $data['teamId']);
    }

    public function testMemberCannotCreateProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $memberToken = $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'bob@example.com']));

        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$memberToken,
        ], content: json_encode(['name' => 'Mini Jira']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testOutsiderCannotCreateProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['name' => 'Mini Jira']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testCreateProjectRejectsBlankName(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => '']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateProjectRequiresAuthentication(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/projects", server: ['CONTENT_TYPE' => 'application/json'], content: json_encode(['name' => 'Mini Jira']));

        self::assertResponseStatusCodeSame(401);
    }
}
