<?php

namespace App\Tests\Analytics\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrackActionTest extends WebTestCase
{
    use AppTestHelpers;
    use ResetsDatabase;

    private const VISITOR = '5b3f3d0e-9c64-4c5e-9a52-6f1f0a6d7a11';

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = $this->client->getContainer()->get(Connection::class);
        $this->resetDatabase($this->connection);
    }

    public function testAnonymousVisitIsRecordedWithoutAuthentication(): void
    {
        $this->track(self::VISITOR);

        self::assertResponseStatusCodeSame(204);
        $rows = $this->connection->fetchAllAssociative('SELECT visitor_id, bucket_hour, user_id FROM visitor_activity');
        self::assertCount(1, $rows);
        self::assertSame(self::VISITOR, $rows[0]['visitor_id']);
        self::assertNull($rows[0]['user_id']);
        self::assertStringEndsWith(':00:00', $rows[0]['bucket_hour']);
    }

    public function testRepeatedVisitsInTheSameHourCountOnce(): void
    {
        $this->track(self::VISITOR);
        $this->track(self::VISITOR);
        $this->track(strtoupper(self::VISITOR));

        self::assertSame(1, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM visitor_activity'));
    }

    public function testDifferentVisitorsAreRecordedSeparately(): void
    {
        $this->track(self::VISITOR);
        $this->track('0a7e5f34-2d86-4a0e-8d3c-1b9d7c2e4f55');

        self::assertSame(2, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM visitor_activity'));
    }

    public function testAuthenticatedVisitIsLinkedToTheUser(): void
    {
        [$token, $userId] = $this->registerAndLoginWithId('alice@example.com');

        $this->track(self::VISITOR, $token);

        self::assertResponseStatusCodeSame(204);
        self::assertSame($userId, $this->connection->fetchOne('SELECT user_id FROM visitor_activity'));
    }

    public function testLaterAnonymousVisitDoesNotEraseTheLinkedUser(): void
    {
        [$token, $userId] = $this->registerAndLoginWithId('alice@example.com');

        $this->track(self::VISITOR, $token);
        $this->track(self::VISITOR);

        self::assertSame($userId, $this->connection->fetchOne('SELECT user_id FROM visitor_activity'));
    }

    public function testInvalidTokenIsTreatedAsAnonymousInsteadOfRejected(): void
    {
        $this->track(self::VISITOR, 'not-a-valid-jwt');

        self::assertResponseStatusCodeSame(204);
        self::assertNull($this->connection->fetchOne('SELECT user_id FROM visitor_activity'));
    }

    public function testInvalidVisitorIdIsRejected(): void
    {
        $this->track('not-a-uuid');

        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM visitor_activity'));
    }

    public function testMissingVisitorIdIsRejected(): void
    {
        $this->client->request('POST', '/api/track', server: ['CONTENT_TYPE' => 'application/json'], content: '{}');

        self::assertResponseStatusCodeSame(422);
    }

    private function track(string $visitorId, ?string $token = null): void
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if (null !== $token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $this->client->request('POST', '/api/track', server: $server, content: json_encode(['visitorId' => $visitorId]));
    }
}
