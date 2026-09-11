<?php

namespace App\Tests\Auth\Infrastructure\Symfony\Controllers;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->getContainer()->get(Connection::class)->executeStatement('DELETE FROM "user"');
    }

    private function createUser(string $email = 'jane@example.com', string $password = 'password123'): void
    {
        $container = $this->client->getContainer();
        $users = $container->get(UserRepositoryInterface::class);
        $hasher = $container->get(PasswordHasherInterface::class);
        $ids = $container->get(UserIdGeneratorInterface::class);

        $user = User::register($ids->generate(), new Email($email), 'Jane', $hasher->hash($password));
        $users->save($user);
    }

    public function testLoginReturnsJwtToken(): void
    {
        $this->createUser();

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('token', $data);
        self::assertNotEmpty($data['token']);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $this->createUser();

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]));

        self::assertResponseStatusCodeSame(401);
    }

    public function testMeRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testMeReturnsCurrentUserWithValidToken(): void
    {
        $this->createUser();

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]));
        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];

        $this->client->request('GET', '/api/me', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('jane@example.com', $data['email']);
    }
}
