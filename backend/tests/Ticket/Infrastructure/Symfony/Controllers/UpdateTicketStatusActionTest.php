<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpdateTicketStatusActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanUpdateStatus(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/status", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['status' => 'in_progress']));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('in_progress', $data['status']);
    }

    public function testInvalidStatusIsRejected(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/status", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['status' => 'not-a-status']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotUpdateStatus(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/status", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['status' => 'done']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testUnknownTicketReturns404(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->client->request('PATCH', '/api/tickets/00000000-0000-7000-8000-000000000000/status', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['status' => 'done']));

        self::assertResponseStatusCodeSame(404);
    }
}
