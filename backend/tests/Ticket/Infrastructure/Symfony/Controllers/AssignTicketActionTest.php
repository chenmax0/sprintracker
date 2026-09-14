<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssignTicketActionTest extends WebTestCase
{
    use ResetsDatabase;
    use TicketTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testOwnerCanReassignTicket(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        [, $bobId] = $this->registerAndLoginWithId('bob@example.com');
        $teamId = $this->createTeam($ownerToken);
        $this->addMember($ownerToken, $teamId, 'bob@example.com');
        $projectId = $this->createProject($ownerToken, $teamId);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('POST', "/api/tickets/$ticketId/assign", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['assigneeId' => $bobId]));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($bobId, $data['assigneeId']);
    }

    public function testCanUnassignByPassingNull(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        [, $bobId] = $this->registerAndLoginWithId('bob@example.com');
        $teamId = $this->createTeam($ownerToken);
        $this->addMember($ownerToken, $teamId, 'bob@example.com');
        $projectId = $this->createProject($ownerToken, $teamId);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('POST', "/api/tickets/$ticketId/assign", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['assigneeId' => $bobId]));

        $this->client->request('POST', "/api/tickets/$ticketId/assign", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['assigneeId' => null]));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertNull($data['assigneeId']);
    }

    public function testNonMemberCannotAssign(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('POST', "/api/tickets/$ticketId/assign", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['assigneeId' => null]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAssigningUnknownTicketReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');

        $this->client->request('POST', '/api/tickets/00000000-0000-7000-8000-000000000000/assign', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['assigneeId' => null]));

        self::assertResponseStatusCodeSame(404);
    }
}
