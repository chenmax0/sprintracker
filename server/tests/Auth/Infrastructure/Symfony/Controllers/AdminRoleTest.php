<?php

namespace App\Tests\Auth\Infrastructure\Symfony\Controllers;

use App\Auth\Application\PromoteUserToAdmin\PromoteUserToAdminHandler;
use App\Auth\Application\PromoteUserToAdmin\PromoteUserToAdminPayload;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminRoleTest extends WebTestCase
{
    use AppTestHelpers;
    use ResetsDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMeReportsWhetherTheUserIsAnAdmin(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->me($token);
        self::assertFalse(json_decode($this->client->getResponse()->getContent(), true)['isAdmin']);

        $this->client->getContainer()->get(PromoteUserToAdminHandler::class)->handle(new PromoteUserToAdminPayload('jane@example.com'));

        // Roles are reloaded from the database on every request: no new login needed.
        $this->me($token);
        self::assertTrue(json_decode($this->client->getResponse()->getContent(), true)['isAdmin']);
    }

    public function testPromotingTwiceKeepsASingleAdminRole(): void
    {
        $this->registerAndLogin('jane@example.com');
        $handler = $this->client->getContainer()->get(PromoteUserToAdminHandler::class);

        $handler->handle(new PromoteUserToAdminPayload('jane@example.com'));
        $handler->handle(new PromoteUserToAdminPayload('jane@example.com'));

        $roles = json_decode($this->client->getContainer()->get(Connection::class)->fetchOne('SELECT roles FROM "user"'), true);
        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], $roles);
    }

    public function testPromotingAnUnknownUserFails(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->client->getContainer()->get(PromoteUserToAdminHandler::class)->handle(new PromoteUserToAdminPayload('nobody@example.com'));
    }

    private function me(string $token): void
    {
        $this->client->request('GET', '/api/me', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
    }
}
