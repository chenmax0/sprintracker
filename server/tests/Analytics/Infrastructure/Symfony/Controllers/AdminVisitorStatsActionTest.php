<?php

namespace App\Tests\Analytics\Infrastructure\Symfony\Controllers;

use App\Auth\Application\Port\PasswordHasherInterface;
use App\Auth\Application\Port\UserIdGeneratorInterface;
use App\Auth\Domain\Email;
use App\Auth\Domain\User;
use App\Auth\Domain\UserRepositoryInterface;
use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminVisitorStatsActionTest extends WebTestCase
{
    use AppTestHelpers;
    use ResetsDatabase;

    private const A = '00000000-0000-4000-8000-00000000000a';
    private const B = '00000000-0000-4000-8000-00000000000b';
    private const C = '00000000-0000-4000-8000-00000000000c';

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = $this->client->getContainer()->get(Connection::class);
        $this->resetDatabase($this->connection);
    }

    public function testAnonymousIsUnauthorized(): void
    {
        $this->client->request('GET', '/api/admin/analytics/visitors');

        self::assertResponseStatusCodeSame(401);
    }

    public function testRegularUserIsForbidden(): void
    {
        $token = $this->registerAndLogin('bob@example.com');

        $this->stats($token, []);

        self::assertResponseStatusCodeSame(403);
    }

    public function testDailyCountsAreDistinctPerVisitorAndZeroFilled(): void
    {
        $token = $this->registerAdminAndLogin();
        // Visitor A is active twice on the 10th (counts once for the day) and again on the 12th.
        $this->activity(self::A, '2026-07-10 08:00:00');
        $this->activity(self::A, '2026-07-10 15:00:00');
        $this->activity(self::B, '2026-07-10 09:00:00');
        $this->activity(self::A, '2026-07-12 09:00:00');

        $data = $this->stats($token, ['granularity' => 'day', 'from' => '2026-07-10', 'to' => '2026-07-12']);

        self::assertResponseStatusCodeSame(200);
        self::assertSame(
            [
                ['start' => '2026-07-10T00:00:00', 'visitors' => 2, 'users' => 0],
                ['start' => '2026-07-11T00:00:00', 'visitors' => 0, 'users' => 0],
                ['start' => '2026-07-12T00:00:00', 'visitors' => 1, 'users' => 0],
            ],
            $data['series'],
        );
        // A and B over the whole window: 2 unique people, not 3 (the sum of the days).
        self::assertSame(2, $data['totals']['visitors']);
    }

    public function testHourlyCounts(): void
    {
        $token = $this->registerAdminAndLogin();
        $this->activity(self::A, '2026-07-10 08:00:00');
        $this->activity(self::B, '2026-07-10 08:00:00');
        $this->activity(self::A, '2026-07-10 09:00:00');

        $data = $this->stats($token, ['granularity' => 'hour', 'from' => '2026-07-10', 'to' => '2026-07-10']);

        self::assertCount(24, $data['series']);
        self::assertSame(2, $data['series'][8]['visitors']);
        self::assertSame(1, $data['series'][9]['visitors']);
        self::assertSame(0, $data['series'][10]['visitors']);
        self::assertSame(2, $data['totals']['visitors']);
    }

    public function testDaysAreBucketedInTheRequestedTimezone(): void
    {
        $token = $this->registerAdminAndLogin();
        // 22:30 UTC on the 10th is already 00:30 on the 11th in Paris (UTC+2 in July).
        $this->activity(self::A, '2026-07-10 22:00:00');

        $utc = $this->stats($token, ['granularity' => 'day', 'from' => '2026-07-10', 'to' => '2026-07-11', 'timezone' => 'UTC']);
        $paris = $this->stats($token, ['granularity' => 'day', 'from' => '2026-07-10', 'to' => '2026-07-11', 'timezone' => 'Europe/Paris']);

        self::assertSame([1, 0], array_column($utc['series'], 'visitors'));
        self::assertSame([0, 1], array_column($paris['series'], 'visitors'));
    }

    public function testSignedInUsersAreCountedSeparately(): void
    {
        $token = $this->registerAdminAndLogin();
        $userId = (string) $this->connection->fetchOne('SELECT id FROM "user" LIMIT 1');
        $this->activity(self::A, '2026-07-10 08:00:00', $userId);
        $this->activity(self::B, '2026-07-10 08:00:00');
        // Same signed-in user on a second device: two visitors, one user.
        $this->activity(self::C, '2026-07-10 09:00:00', $userId);

        $data = $this->stats($token, ['granularity' => 'day', 'from' => '2026-07-10', 'to' => '2026-07-10']);

        self::assertSame(['start' => '2026-07-10T00:00:00', 'visitors' => 3, 'users' => 1], $data['series'][0]);
        self::assertSame(['visitors' => 3, 'users' => 1], $data['totals']);
    }

    public function testWeeklyBucketsStartOnMondayAndFirstBucketIsWidened(): void
    {
        $token = $this->registerAdminAndLogin();
        // 2026-07-08 is a Wednesday, 2026-07-06 the Monday of that week.
        $this->activity(self::A, '2026-07-06 10:00:00');
        $this->activity(self::B, '2026-07-14 10:00:00');

        $data = $this->stats($token, ['granularity' => 'week', 'from' => '2026-07-08', 'to' => '2026-07-14']);

        self::assertSame(
            [
                ['start' => '2026-07-06T00:00:00', 'visitors' => 1, 'users' => 0],
                ['start' => '2026-07-13T00:00:00', 'visitors' => 1, 'users' => 0],
            ],
            $data['series'],
        );
    }

    public function testMonthlyBuckets(): void
    {
        $token = $this->registerAdminAndLogin();
        $this->activity(self::A, '2026-06-30 10:00:00');
        $this->activity(self::A, '2026-07-01 10:00:00');
        $this->activity(self::B, '2026-07-20 10:00:00');

        $data = $this->stats($token, ['granularity' => 'month', 'from' => '2026-06-01', 'to' => '2026-07-31']);

        self::assertSame([1, 2], array_column($data['series'], 'visitors'));
        self::assertSame(2, $data['totals']['visitors']);
    }

    public function testDefaultsToTheLast30DaysByDay(): void
    {
        $token = $this->registerAdminAndLogin();

        $data = $this->stats($token, []);

        self::assertResponseStatusCodeSame(200);
        self::assertSame('day', $data['granularity']);
        self::assertCount(30, $data['series']);
    }

    #[DataProvider('invalidQueries')]
    public function testInvalidQueryIsRejected(array $query): void
    {
        $token = $this->registerAdminAndLogin();

        $this->stats($token, $query);

        self::assertResponseStatusCodeSame(422);
    }

    public static function invalidQueries(): iterable
    {
        yield 'unknown granularity' => [['granularity' => 'minute']];
        yield 'unknown timezone' => [['timezone' => 'Mars/Olympus']];
        yield 'malformed date' => [['from' => '10/07/2026', 'to' => '2026-07-12']];
        yield 'impossible date' => [['from' => '2026-02-31', 'to' => '2026-03-05']];
        yield 'to before from' => [['from' => '2026-07-12', 'to' => '2026-07-10']];
        yield 'hourly window too wide' => [['granularity' => 'hour', 'from' => '2026-01-01', 'to' => '2026-03-01']];
    }

    private function activity(string $visitorId, string $bucketHourUtc, ?string $userId = null): void
    {
        $this->connection->executeStatement(
            'INSERT INTO visitor_activity (visitor_id, bucket_hour, user_id) VALUES (:v, :h, :u)',
            ['v' => $visitorId, 'h' => $bucketHourUtc, 'u' => $userId],
        );
    }

    private function registerAdminAndLogin(): string
    {
        $container = $this->client->getContainer();
        $user = User::register(
            $container->get(UserIdGeneratorInterface::class)->generate(),
            new Email('admin@example.com'),
            'Admin',
            $container->get(PasswordHasherInterface::class)->hash('password123'),
        );
        $user->grantAdmin();
        $container->get(UserRepositoryInterface::class)->save($user);

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]));

        return json_decode($this->client->getResponse()->getContent(), true)['token'];
    }

    /**
     * @param array<string, string> $query
     */
    private function stats(string $token, array $query): array
    {
        $this->client->request('GET', '/api/admin/analytics/visitors?'.http_build_query($query), server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }
}
