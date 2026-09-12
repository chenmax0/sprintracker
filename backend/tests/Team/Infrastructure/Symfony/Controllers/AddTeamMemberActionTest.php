<?php

namespace App\Tests\Team\Infrastructure\Symfony\Controllers;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AddTeamMemberActionTest extends WebTestCase
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

    private function createTeam(string $ownerToken, string $name = 'Sprint Squad'): string
    {
        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => $name]));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    public function testOwnerCanAddExistingUserByEmail(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'bob@example.com']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('member', $data['role']);
    }

    public function testCannotAddSameMemberTwice(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'bob@example.com']));
        self::assertResponseStatusCodeSame(201);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'bob@example.com']));
        self::assertResponseStatusCodeSame(409);
    }

    public function testCannotAddUnknownEmail(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'ghost@example.com']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotAddSomeoneToTeam(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['email' => 'bob@example.com']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAddingToUnknownTeamReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $this->registerAndLogin('bob@example.com');

        $this->client->request('POST', '/api/teams/00000000-0000-7000-8000-000000000000/members', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'bob@example.com']));

        self::assertResponseStatusCodeSame(404);
    }

    public function testMembersCanListTeamMembers(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('POST', "/api/teams/$teamId/members", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['email' => 'bob@example.com']));

        $this->client->request('GET', "/api/teams/$teamId/members", server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ]);

        self::assertResponseStatusCodeSame(200);
        $members = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $members);
    }

    public function testNonMembersCannotListTeamMembers(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('GET', "/api/teams/$teamId/members", server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ]);

        self::assertResponseStatusCodeSame(403);
    }
}
