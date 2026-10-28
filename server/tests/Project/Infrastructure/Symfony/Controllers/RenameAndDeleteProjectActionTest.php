<?php

namespace App\Tests\Project\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RenameAndDeleteProjectActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testOwnerCanRenameProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('PATCH', "/api/projects/$projectId", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Renamed']));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Renamed', $data['name']);
        self::assertTrue($data['isOwner']);
    }

    public function testMemberCannotRenameProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $memberToken = $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);
        $this->addMember($ownerToken, $teamId, 'bob@example.com');
        $projectId = $this->createProject($ownerToken, $teamId);

        $this->client->request('PATCH', "/api/projects/$projectId", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$memberToken,
        ], content: json_encode(['name' => 'Renamed']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testRenameRejectsBlankName(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('PATCH', "/api/projects/$projectId", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => '']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testOwnerCanDeleteProjectWithItsSprintsAndTickets(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);
        $ticketId = $this->createTicket($ownerToken, $projectId);
        $this->client->request('POST', "/api/tickets/$ticketId/comments", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['content' => 'A comment']));

        $this->client->request('DELETE', "/api/projects/$projectId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(204);

        $this->client->request('GET', "/api/projects/$projectId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        self::assertResponseStatusCodeSame(404);

        $connection = $this->client->getContainer()->get(Connection::class);
        self::assertFalse($connection->fetchOne('SELECT 1 FROM sprint WHERE id = :id', ['id' => $sprintId]));
        self::assertFalse($connection->fetchOne('SELECT 1 FROM ticket WHERE id = :id', ['id' => $ticketId]));
        self::assertFalse($connection->fetchOne('SELECT 1 FROM comment WHERE ticket_id = :id', ['id' => $ticketId]));
    }

    public function testMemberCannotDeleteProject(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $memberToken = $this->registerAndLogin('bob@example.com');
        $teamId = $this->createTeam($ownerToken);
        $this->addMember($ownerToken, $teamId, 'bob@example.com');
        $projectId = $this->createProject($ownerToken, $teamId);

        $this->client->request('DELETE', "/api/projects/$projectId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$memberToken]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testDeletingAnUnknownProjectReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');

        $this->client->request('DELETE', '/api/projects/00000000-0000-7000-8000-000000000000', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(404);
    }
}
