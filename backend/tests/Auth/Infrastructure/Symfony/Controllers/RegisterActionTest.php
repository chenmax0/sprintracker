<?php

namespace App\Tests\Auth\Infrastructure\Symfony\Controllers;

use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterActionTest extends WebTestCase
{
    use ResetsDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testRegisterCreatesUser(): void
    {
        $this->client->request('POST', '/api/register', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'jane@example.com',
            'name' => 'Jane',
            'password' => 'password123',
        ]));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('jane@example.com', $data['email']);
        self::assertSame('Jane', $data['name']);
        self::assertArrayNotHasKey('password', $data);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $payload = json_encode([
            'email' => 'jane@example.com',
            'name' => 'Jane',
            'password' => 'password123',
        ]);

        $this->client->request('POST', '/api/register', server: ['CONTENT_TYPE' => 'application/json'], content: $payload);
        self::assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/api/register', server: ['CONTENT_TYPE' => 'application/json'], content: $payload);
        self::assertResponseStatusCodeSame(422);
    }

    public function testRegisterRejectsInvalidPayload(): void
    {
        $this->client->request('POST', '/api/register', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'not-an-email',
            'name' => '',
            'password' => 'short',
        ]));

        self::assertResponseStatusCodeSame(422);
    }
}
