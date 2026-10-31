<?php

namespace App\Tests\Sprint\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompleteSprintActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testCompletingASprintOnlyCompletesItAndLaunchesNothing(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);

        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(200);
        $completed = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($sprintId, $completed['id']);
        self::assertSame('completed', $completed['status']);

        $this->client->request('GET', "/api/projects/$projectId/sprints", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        $sprints = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $sprints);
    }

    public function testUnfinishedTicketsStayPutUntilTheNextSprintIsLaunched(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);
        $unfinishedTicketId = $this->createTicketInSprint($ownerToken, $projectId, 'Unfinished ticket', $sprintId);
        $doneTicketId = $this->createTicketInSprint($ownerToken, $projectId, 'Done ticket', $sprintId);
        $this->client->request('PATCH', "/api/tickets/$doneTicketId/column", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['columnId' => $this->lastColumnId($ownerToken, $projectId)]));

        $this->completeSprint($ownerToken, $sprintId);

        self::assertSame($sprintId, $this->ticketSprintId($ownerToken, $unfinishedTicketId));

        $this->client->request('POST', "/api/projects/$projectId/sprints", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['startDate' => '2026-09-29', 'endDate' => '2026-10-13']));
        self::assertResponseStatusCodeSame(201);
        $nextSprint = json_decode($this->client->getResponse()->getContent(), true);

        self::assertSame($nextSprint['id'], $this->ticketSprintId($ownerToken, $unfinishedTicketId));
        self::assertSame($sprintId, $this->ticketSprintId($ownerToken, $doneTicketId));

        $this->client->request('GET', "/api/tickets/$unfinishedTicketId/sprint-history", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        self::assertResponseStatusCodeSame(200);
        $history = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame([$sprintId], array_column($history, 'sprintId'));
    }

    public function testCompletingAnAlreadyCompletedSprintFails(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);
        $this->completeSprint($ownerToken, $sprintId);

        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(409);
    }

    public function testNonMemberCannotCompleteSprint(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);

        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testCompletingAnUnknownSprintReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');

        $this->client->request('POST', '/api/sprints/00000000-0000-7000-8000-000000000000/complete', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(404);
    }

    private function createTicketInSprint(string $token, string $projectId, string $title, string $sprintId): string
    {
        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['title' => $title, 'sprintId' => $sprintId]));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function ticketSprintId(string $token, string $ticketId): ?string
    {
        $this->client->request('GET', "/api/tickets/$ticketId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        return json_decode($this->client->getResponse()->getContent(), true)['sprintId'];
    }
}
