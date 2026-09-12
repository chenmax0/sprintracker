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

class CreateTeamActionTest extends WebTestCase
{
    use ResetsDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    private function authenticate(string $email = 'jane@example.com', string $password = 'password123'): string
    {
        $container = $this->client->getContainer();
        $users = $container->get(UserRepositoryInterface::class);
        $hasher = $container->get(PasswordHasherInterface::class);
        $ids = $container->get(UserIdGeneratorInterface::class);

        $users->save(User::register($ids->generate(), new Email($email), 'Jane', $hasher->hash($password)));

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        return json_decode($this->client->getResponse()->getContent(), true)['token'];
    }

    public function testCreateTeamRequiresAuthentication(): void
    {
        $this->client->request('POST', '/api/teams', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode(['name' => 'Sprint Squad']));

        self::assertResponseStatusCodeSame(401);
    }

    public function testCreateTeamMakesCreatorOwner(): void
    {
        $token = $this->authenticate();

        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['name' => 'Sprint Squad']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Sprint Squad', $data['name']);
        self::assertNotEmpty($data['id']);

        $membership = $this->client->getContainer()->get(Connection::class)->fetchAssociative(
            'SELECT role FROM team_membership WHERE team_id = :team_id',
            ['team_id' => $data['id']],
        );

        self::assertSame('owner', $membership['role']);
    }

    public function testCreateTeamRejectsBlankName(): void
    {
        $token = $this->authenticate();

        $this->client->request('POST', '/api/teams', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['name' => '']));

        self::assertResponseStatusCodeSame(422);
    }
}
