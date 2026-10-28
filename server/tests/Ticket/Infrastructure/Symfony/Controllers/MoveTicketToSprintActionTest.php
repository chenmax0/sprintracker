<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MoveTicketToSprintActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanMoveABacklogTicketIntoTheActiveSprint(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);
        $sprintId = $this->createSprint($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/sprint", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['sprintId' => $sprintId]));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($sprintId, $data['sprintId']);
    }

    public function testCanMoveBackToBacklogByPassingNull(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);
        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Ticket', 'sprintId' => $sprintId]));
        $ticketId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('PATCH', "/api/tickets/$ticketId/sprint", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['sprintId' => null]));

        self::assertResponseStatusCodeSame(200);
        self::assertNull(json_decode($this->client->getResponse()->getContent(), true)['sprintId']);
    }

    public function testRejectsASprintFromAnotherProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);
        $otherProjectId = $this->createTeamAndProject($ownerToken);
        $otherSprintId = $this->createSprint($ownerToken, $otherProjectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/sprint", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['sprintId' => $otherSprintId]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotMoveTicket(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('PATCH', "/api/tickets/$ticketId/sprint", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['sprintId' => null]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testMovingAnUnknownTicketReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');

        $this->client->request('PATCH', '/api/tickets/00000000-0000-7000-8000-000000000000/sprint', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['sprintId' => null]));

        self::assertResponseStatusCodeSame(404);
    }
}
