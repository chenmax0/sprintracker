<?php

namespace App\Tests\Board\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BoardColumnActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testNewProjectStartsWithThreeDefaultColumnsInOrder(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $columns = $this->listColumns($ownerToken, $projectId);

        self::assertCount(3, $columns);
        self::assertSame(['À faire', 'En cours', 'Terminé'], array_column($columns, 'name'));
        self::assertSame([0, 1, 2], array_column($columns, 'position'));
    }

    public function testMemberCanCreateAColumn(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/columns", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Cadrage']));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Cadrage', $data['name']);
        self::assertSame(3, $data['position']);
    }

    public function testMemberCanRenameAColumn(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $columnId = $this->listColumns($ownerToken, $projectId)[0]['id'];

        $this->client->request('PATCH', "/api/columns/$columnId", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['name' => 'Backlog trié']));

        self::assertResponseStatusCodeSame(200);
        self::assertSame('Backlog trié', json_decode($this->client->getResponse()->getContent(), true)['name']);
    }

    public function testMemberCanReorderColumns(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $columns = $this->listColumns($ownerToken, $projectId);
        $reordered = [$columns[1]['id'], $columns[0]['id'], $columns[2]['id']];

        $this->client->request('PUT', "/api/projects/$projectId/columns/order", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['columnIds' => $reordered]));

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($reordered, array_column($data, 'id'));
        self::assertSame([0, 1, 2], array_column($data, 'position'));
    }

    public function testReorderRejectsAnIncompleteOrUnknownSet(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $columns = $this->listColumns($ownerToken, $projectId);

        $this->client->request('PUT', "/api/projects/$projectId/columns/order", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['columnIds' => [$columns[0]['id']]]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testCanDeleteAnEmptyColumn(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $columnId = $this->listColumns($ownerToken, $projectId)[2]['id'];

        $this->client->request('DELETE', "/api/columns/$columnId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(204);
        self::assertCount(2, $this->listColumns($ownerToken, $projectId));
    }

    public function testCannotDeleteAColumnWithTickets(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $this->createTicket($ownerToken, $projectId);
        $columnId = $this->listColumns($ownerToken, $projectId)[0]['id'];

        $this->client->request('DELETE', "/api/columns/$columnId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(409);
    }

    public function testCannotDeleteTheLastColumn(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $columns = $this->listColumns($ownerToken, $projectId);

        foreach (array_slice($columns, 0, 2) as $column) {
            $this->client->request('DELETE', "/api/columns/{$column['id']}", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
            self::assertResponseStatusCodeSame(204);
        }

        $this->client->request('DELETE', "/api/columns/{$columns[2]['id']}", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(409);
    }

    public function testNonMemberCannotManageColumns(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('POST', "/api/projects/$projectId/columns", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['name' => 'Cadrage']));

        self::assertResponseStatusCodeSame(403);
    }
}
