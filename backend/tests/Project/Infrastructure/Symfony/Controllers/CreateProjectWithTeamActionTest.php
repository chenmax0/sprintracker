<?php

namespace App\Tests\Project\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateProjectWithTeamActionTest extends WebTestCase
{
    use ResetsDatabase;
    use AppTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testCreatingAProjectCreatesItsOwnTeamWithTheRequesterAsOwner(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->client->request('POST', '/api/projects', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['name' => 'Mini Jira']));

        self::assertResponseStatusCodeSame(201);
        $project = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Mini Jira', $project['name']);
        self::assertNotEmpty($project['teamId']);

        // The requester must already be the owner of the implicit team,
        // otherwise creating a second project under it would fail with 403.
        $this->client->request('POST', "/api/teams/{$project['teamId']}/projects", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['name' => 'Second project']));
        self::assertResponseStatusCodeSame(201);
    }

    public function testRejectsBlankName(): void
    {
        $token = $this->registerAndLogin('jane@example.com');

        $this->client->request('POST', '/api/projects', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['name' => '']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testRequiresAuthentication(): void
    {
        $this->client->request('POST', '/api/projects', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode(['name' => 'Mini Jira']));

        self::assertResponseStatusCodeSame(401);
    }
}
