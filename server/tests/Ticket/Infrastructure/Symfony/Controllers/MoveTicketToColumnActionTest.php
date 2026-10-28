<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MoveTicketToColumnActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanMoveTicketToAnotherColumn(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);
        $lastColumnId = $this->lastColumnId($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/column", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['columnId' => $lastColumnId]));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($lastColumnId, $data['columnId']);
    }

    public function testColumnFromAnotherProjectIsRejected(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);
        $otherProjectId = $this->createTeamAndProject($ownerToken);
        $otherColumnId = $this->lastColumnId($ownerToken, $otherProjectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/column", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['columnId' => $otherColumnId]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotMoveTicket(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);
        $lastColumnId = $this->lastColumnId($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/column", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['columnId' => $lastColumnId]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testUnknownTicketReturns404(): void
    {
        $token = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($token);
        $lastColumnId = $this->lastColumnId($token, $projectId);

        $this->client->request('PATCH', '/api/tickets/00000000-0000-7000-8000-000000000000/column', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['columnId' => $lastColumnId]));

        self::assertResponseStatusCodeSame(404);
    }
}
