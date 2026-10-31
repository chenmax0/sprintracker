<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\AppTestHelpers;
use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BacklogActionTest extends WebTestCase
{
    use AppTestHelpers;
    use ResetsDatabase;

    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->connection = $this->client->getContainer()->get(Connection::class);
        $this->resetDatabase($this->connection);
    }

    public function testBacklogHoldsTicketsOutsideAnySprintOnly(): void
    {
        [$token, $projectId] = $this->setUpProject();
        $sprintId = $this->createSprint($token, $projectId);
        $this->createTicket($token, $projectId, 'In backlog');
        $this->createTicketInSprint($token, $projectId, 'On the board', $sprintId);

        $data = $this->backlog($token, $projectId);

        self::assertResponseStatusCodeSame(200);
        self::assertSame(['In backlog'], array_column($data['items'], 'title'));
        self::assertSame(1, $data['total']);
    }

    public function testUnfinishedTicketsOfACompletedSprintAreInTheBacklogButDoneOnesAreNot(): void
    {
        [$token, $projectId] = $this->setUpProject();
        $sprintId = $this->createSprint($token, $projectId);
        $this->createTicketInSprint($token, $projectId, 'Unfinished', $sprintId);
        $doneId = $this->createTicketInSprint($token, $projectId, 'Done', $sprintId);
        $this->client->request('PATCH', "/api/tickets/$doneId/column", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['columnId' => $this->lastColumnId($token, $projectId)]));
        $this->completeSprint($token, $sprintId);

        $data = $this->backlog($token, $projectId);

        self::assertSame(['Unfinished'], array_column($data['items'], 'title'));
    }

    public function testOldestFirstByDefaultAndSortable(): void
    {
        [$token, $projectId] = $this->setUpProject();
        $recent = $this->createTicket($token, $projectId, 'Recent');
        $old = $this->createTicket($token, $projectId, 'Old');
        $this->setCreatedAt($recent, '2026-10-01 10:00:00');
        $this->setCreatedAt($old, '2026-03-01 10:00:00');

        self::assertSame(['Old', 'Recent'], array_column($this->backlog($token, $projectId)['items'], 'title'));
        self::assertSame(['Recent', 'Old'], array_column($this->backlog($token, $projectId, ['sort' => 'newest'])['items'], 'title'));
        self::assertSame('2026-03-01T10:00:00+00:00', $this->backlog($token, $projectId)['items'][0]['createdAt']);
    }

    public function testMostCarriedOverSort(): void
    {
        [$token, $projectId] = $this->setUpProject();
        $this->createTicket($token, $projectId, 'Never carried');
        $carried = $this->createTicket($token, $projectId, 'Carried');
        $sprintId = $this->createSprint($token, $projectId);
        $this->client->request('PATCH', "/api/tickets/$carried/sprint", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['sprintId' => $sprintId]));
        $this->completeSprint($token, $sprintId);
        $this->createSprint($token, $projectId);

        $titles = array_column($this->backlog($token, $projectId, ['sort' => 'most_carried_over'])['items'], 'title');

        // "Carried" moved on to the new sprint, so only the never-carried one is left.
        self::assertSame(['Never carried'], $titles);
    }

    public function testSearchByTitleAndByNumber(): void
    {
        [$token, $projectId] = $this->setUpProject();
        $this->createTicket($token, $projectId, 'Fix login bug');
        $this->createTicket($token, $projectId, 'Write docs');
        $this->createTicket($token, $projectId, '100% coverage');

        self::assertSame(['Fix login bug'], array_column($this->backlog($token, $projectId, ['search' => 'LOGIN'])['items'], 'title'));
        self::assertSame(['Write docs'], array_column($this->backlog($token, $projectId, ['search' => '#2'])['items'], 'title'));
        self::assertSame(['100% coverage'], array_column($this->backlog($token, $projectId, ['search' => '100%'])['items'], 'title'));
        // A bare "%" is a literal, not a wildcard matching everything.
        self::assertSame(1, $this->backlog($token, $projectId, ['search' => '%'])['total']);
    }

    public function testFilterByAssigneeAndUnassigned(): void
    {
        [$token, $projectId, $userId] = $this->setUpProject();
        $mine = $this->createTicket($token, $projectId, 'Mine');
        $this->createTicket($token, $projectId, 'Nobody');
        $this->client->request('POST', "/api/tickets/$mine/assign", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['assigneeId' => $userId]));

        self::assertSame(['Mine'], array_column($this->backlog($token, $projectId, ['assignee' => $userId])['items'], 'title'));
        self::assertSame(['Nobody'], array_column($this->backlog($token, $projectId, ['assignee' => 'none'])['items'], 'title'));
    }

    public function testPagination(): void
    {
        [$token, $projectId] = $this->setUpProject();
        foreach (range(1, 5) as $i) {
            $this->createTicket($token, $projectId, "Ticket $i");
        }

        $second = $this->backlog($token, $projectId, ['perPage' => 2, 'page' => 2, 'sort' => 'newest']);

        self::assertSame(5, $second['total']);
        self::assertSame(2, $second['page']);
        self::assertSame(['Ticket 3', 'Ticket 2'], array_column($second['items'], 'title'));
        self::assertSame([], $this->backlog($token, $projectId, ['perPage' => 2, 'page' => 9])['items']);
    }

    public function testOtherProjectsTicketsAreNotListed(): void
    {
        [$token, $projectId] = $this->setUpProject();
        $otherProjectId = $this->createTeamAndProject($token);
        $this->createTicket($token, $otherProjectId, 'Elsewhere');

        self::assertSame(0, $this->backlog($token, $projectId)['total']);
    }

    public function testNonMemberIsForbidden(): void
    {
        [, $projectId] = $this->setUpProject();
        $outsider = $this->registerAndLogin('charlie@example.com');

        $this->backlog($outsider, $projectId);

        self::assertResponseStatusCodeSame(403);
    }

    public function testRequiresAuthentication(): void
    {
        [, $projectId] = $this->setUpProject();
        $this->client->getCookieJar()->clear(); // logging in left the JWT cookie in the test browser

        $this->client->request('GET', "/api/projects/$projectId/backlog");

        self::assertResponseStatusCodeSame(401);
    }

    #[DataProvider('invalidQueries')]
    public function testInvalidQueryIsRejected(array $query): void
    {
        [$token, $projectId] = $this->setUpProject();

        $this->backlog($token, $projectId, $query);

        self::assertResponseStatusCodeSame(422);
    }

    public static function invalidQueries(): iterable
    {
        yield 'unknown sort' => [['sort' => 'random']];
        yield 'page zero' => [['page' => 0]];
        yield 'page size too large' => [['perPage' => 101]];
        yield 'assignee not a uuid' => [['assignee' => 'bob']];
    }

    /**
     * @return array{0: string, 1: string, 2: string} [token, projectId, userId]
     */
    private function setUpProject(): array
    {
        [$token, $userId] = $this->registerAndLoginWithId('jane@example.com');

        return [$token, $this->createTeamAndProject($token), $userId];
    }

    private function createTicketInSprint(string $token, string $projectId, string $title, string $sprintId): string
    {
        $this->client->request('POST', "/api/projects/$projectId/tickets", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode(['title' => $title, 'sprintId' => $sprintId]));

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }

    private function setCreatedAt(string $ticketId, string $createdAtUtc): void
    {
        $this->connection->executeStatement('UPDATE ticket SET created_at = :at WHERE id = :id', ['at' => $createdAtUtc, 'id' => $ticketId]);
    }

    private function backlog(string $token, string $projectId, array $query = []): array
    {
        $this->client->request('GET', "/api/projects/$projectId/backlog?".http_build_query($query), server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }
}
