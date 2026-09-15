<?php

namespace App\Tests\Demo\Infrastructure\Symfony\Controllers;

use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DemoActionTest extends WebTestCase
{
    use ResetsDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testDemoIsPubliclyAccessibleWithoutAuthentication(): void
    {
        $container = $this->client->getContainer();
        $connection = $container->get(Connection::class);
        $demoTeamId = $container->getParameter('demo_team_id');

        $connection->executeStatement(
            'INSERT INTO team (id, name) VALUES (:id, :name)',
            ['id' => $demoTeamId, 'name' => 'Sprintracker Demo'],
        );
        $connection->executeStatement(
            'INSERT INTO project (id, team_id, name) VALUES (:id, :team_id, :name)',
            ['id' => '00000000-0000-7000-8000-000000000099', 'team_id' => $demoTeamId, 'name' => 'Site vitrine'],
        );

        // No Authorization header, no cookie: this must work for a fully anonymous visitor.
        $this->client->request('GET', '/api/demo');

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Sprintracker Demo', $data['team']['name']);
        self::assertSame('Site vitrine', $data['project']['name']);
    }

    public function testDemoReturnsNullsWhenNothingIsSeeded(): void
    {
        $this->client->request('GET', '/api/demo');

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertNull($data['team']);
        self::assertNull($data['project']);
        self::assertSame([], $data['sprints']);
        self::assertSame([], $data['tickets']);
    }
}
