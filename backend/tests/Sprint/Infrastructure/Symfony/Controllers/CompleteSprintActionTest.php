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

    public function testCompletingASprintLaunchesTheNextOneAndCarriesOverUnfinishedTickets(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Unfinished ticket', 'sprintId' => $sprintId]));
        $unfinishedTicketId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['title' => 'Done ticket 2', 'sprintId' => $sprintId]));
        $doneTicketId = json_decode($this->client->getResponse()->getContent(), true)['id'];
        $this->client->request('PATCH', "/api/tickets/$doneTicketId/status", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['status' => 'done']));

        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['nextStartDate' => '2026-09-29', 'nextEndDate' => '2026-10-13']));

        self::assertResponseStatusCodeSame(201);
        $nextSprint = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Sprint 2', $nextSprint['name']);
        self::assertSame('active', $nextSprint['status']);

        $this->client->request('GET', "/api/tickets/$unfinishedTicketId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        $unfinished = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($nextSprint['id'], $unfinished['sprintId']);

        $this->client->request('GET', "/api/tickets/$doneTicketId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        $done = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($sprintId, $done['sprintId']);

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

        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['nextStartDate' => '2026-10-13', 'nextEndDate' => '2026-10-27']));

        self::assertResponseStatusCodeSame(409);
    }

    public function testNonMemberCannotCompleteSprint(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $sprintId = $this->createSprint($ownerToken, $projectId);

        $this->client->request('POST', "/api/sprints/$sprintId/complete", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['nextStartDate' => '2026-09-29', 'nextEndDate' => '2026-10-13']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testCompletingAnUnknownSprintReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');

        $this->client->request('POST', '/api/sprints/00000000-0000-7000-8000-000000000000/complete', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['nextStartDate' => '2026-09-29', 'nextEndDate' => '2026-10-13']));

        self::assertResponseStatusCodeSame(404);
    }
}
