<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateTicketActionTest extends WebTestCase
{
    use ResetsDatabase;
    use TicketTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanCreateBacklogTicket(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Fix bug']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Fix bug', $data['title']);
        self::assertNull($data['sprintId']);
        self::assertNull($data['assigneeId']);
        self::assertSame('todo', $data['status']);
    }

    public function testTicketCanBeCreatedInASprintWithAnAssignee(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        [$bobToken, $bobId] = $this->registerAndLoginWithId('bob@example.com');
        $teamId = $this->createTeam($ownerToken);
        $this->addMember($ownerToken, $teamId, 'bob@example.com');
        $projectId = $this->createProject($ownerToken, $teamId);
        $sprintId = $this->createSprint($ownerToken, $projectId);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Fix bug', 'sprintId' => $sprintId, 'assigneeId' => $bobId]));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($sprintId, $data['sprintId']);
        self::assertSame($bobId, $data['assigneeId']);
    }

    public function testCannotAssignToNonProjectMember(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        [, $charlieId] = $this->registerAndLoginWithId('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Fix bug', 'assigneeId' => $charlieId]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testCannotUseSprintFromAnotherProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $teamId = $this->createTeam($ownerToken);
        $projectId = $this->createProject($ownerToken, $teamId);
        $otherProjectId = $this->createProject($ownerToken, $teamId);
        $otherSprintId = $this->createSprint($ownerToken, $otherProjectId);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Fix bug', 'sprintId' => $otherSprintId]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotCreateTicket(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['title' => 'Fix bug']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testBlankTitleIsRejected(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => '']));

        self::assertResponseStatusCodeSame(422);
    }
}
