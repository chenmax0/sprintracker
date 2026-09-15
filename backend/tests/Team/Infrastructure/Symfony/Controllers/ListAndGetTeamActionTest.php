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

class ListAndGetTeamActionTest extends WebTestCase
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

    public function testListOnlyReturnsMyTeams(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');

        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Sprint Squad']));

        $this->client->request('GET', '/api/teams', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        self::assertResponseStatusCodeSame(200);
        self::assertCount(1, json_decode($this->client->getResponse()->getContent(), true));

        $this->client->request('GET', '/api/teams', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);
        self::assertCount(0, json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testShowRequiresMembership(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');

        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Sprint Squad']));
        $teamId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('GET', "/api/teams/$teamId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        self::assertResponseStatusCodeSame(200);

        $this->client->request('GET', "/api/teams/$teamId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testShowUnknownTeamReturns404(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->client->request('GET', '/api/teams/00000000-0000-7000-8000-000000000000', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        self::assertResponseStatusCodeSame(404);
    }
}
