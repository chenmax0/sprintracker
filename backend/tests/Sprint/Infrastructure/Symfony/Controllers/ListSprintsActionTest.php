<?php

namespace App\Tests\Sprint\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListSprintsActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanListSprintsInNumberOrder(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $this->createSprint($ownerToken, $projectId);
        $this->createSprint($ownerToken, $projectId);

        $this->client->request('GET', "/api/projects/$projectId/sprints", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken]);

        self::assertResponseStatusCodeSame(200);
        $sprints = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $sprints);
        self::assertSame('Sprint 1', $sprints[0]['name']);
        self::assertSame('Sprint 2', $sprints[1]['name']);
    }

    public function testOutsiderCannotListSprints(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);

        $this->client->request('GET', "/api/projects/$projectId/sprints", server: ['HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken]);

        self::assertResponseStatusCodeSame(403);
    }
}
