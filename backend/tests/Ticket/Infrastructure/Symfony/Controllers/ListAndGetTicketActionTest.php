<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListAndGetTicketActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanListTickets(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $this->createTicket($ownerToken, $projectId);

        $this->client->request('GET', "/api/projects/$projectId/tickets", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(1, json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testOutsiderCannotListTickets(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('GET', "/api/projects/$projectId/tickets", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testShowRequiresProjectMembership(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('GET', "/api/tickets/$ticketId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        self::assertResponseStatusCodeSame(200);

        $this->client->request('GET', "/api/tickets/$ticketId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testShowUnknownTicketReturns404(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->client->request('GET', '/api/tickets/00000000-0000-7000-8000-000000000000', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testListCommentsRequiresProjectMembership(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('GET', "/api/tickets/$ticketId/comments", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);

        self::assertResponseStatusCodeSame(403);
    }
}
