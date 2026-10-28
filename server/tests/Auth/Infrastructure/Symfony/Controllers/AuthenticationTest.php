<?php

namespace App\Tests\Auth\Infrastructure\Symfony\Controllers;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationTest extends WebTestCase
{
    use ResetsDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
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

    public function testLoginSetsHttpOnlyCookieAndMeWorksWithoutAuthorizationHeader(): void
    {
        $this->createUser();

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]));

        $cookie = $this->client->getCookieJar()->get('BEARER');
        self::assertNotNull($cookie);
        self::assertTrue($cookie->isHttpOnly());

        // No Authorization header here - the cookie set by login must carry the request.
        $this->client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('jane@example.com', $data['email']);
    }

    public function testLogoutClearsTheCookieAndRevokesAccess(): void
    {
        $this->createUser();

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]));

        $this->client->request('POST', '/api/logout');
        self::assertResponseStatusCodeSame(204);

        $this->client->request('GET', '/api/me');
        self::assertResponseStatusCodeSame(401);
    }
}
