<?php

namespace App\Tests\Project\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListAndGetProjectActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanListProjects(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $teamId = $this->createTeam($ownerToken);
        $this->createProject($ownerToken, $teamId);

        $this->client->request('GET', "/api/teams/$teamId/projects", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(1, json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testOutsiderCannotListProjects(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $teamId = $this->createTeam($ownerToken);

        $this->client->request('GET', "/api/teams/$teamId/projects", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testShowRequiresMembership(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('GET', "/api/projects/$projectId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);
        self::assertResponseStatusCodeSame(200);

        $this->client->request('GET', "/api/projects/$projectId", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);
        self::assertResponseStatusCodeSame(403);
    }

    public function testShowUnknownProjectReturns404(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->client->request('GET', '/api/projects/00000000-0000-7000-8000-000000000000', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testListMineReturnsProjectsAcrossAllOfTheMembersTeams(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $this->createTeamAndProject($ownerToken);
        $this->createTeamAndProject($ownerToken);
        $this->createTeamAndProject($outsiderToken);

        $this->client->request('GET', '/api/projects', server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(200);
        $projects = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $projects);
        self::assertArrayHasKey('teamName', $projects[0]);
    }
}
