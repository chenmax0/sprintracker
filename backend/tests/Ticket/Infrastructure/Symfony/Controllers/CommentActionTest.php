<?php

namespace App\Tests\Ticket\Infrastructure\Symfony\Controllers;

use App\Tests\ResetsDatabase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentActionTest extends WebTestCase
{
    use ResetsDatabase;
    use TicketTestHelpers;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase($this->client->getContainer()->get(Connection::class));
    }

    public function testMemberCanAddAndListComments(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('POST', "/api/tickets/$ticketId/comments", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['content' => 'Looking into it']));

        self::assertResponseStatusCodeSame(201);
        $comment = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Looking into it', $comment['content']);

        $this->client->request('GET', "/api/tickets/$ticketId/comments", server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ]);

        self::assertResponseStatusCodeSame(200);
        $comments = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(1, $comments);
        self::assertSame('Looking into it', $comments[0]['content']);
    }

    public function testBlankCommentIsRejected(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('POST', "/api/tickets/$ticketId/comments", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['content' => '']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testNonMemberCannotComment(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');
        $outsiderToken = $this->registerAndLogin('charlie@example.com');
        $projectId = $this->createTeamAndProject($ownerToken);
        $ticketId = $this->createTicket($ownerToken, $projectId);

        $this->client->request('POST', "/api/tickets/$ticketId/comments", server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$outsiderToken,
        ], content: json_encode(['content' => 'Sneaky comment']));

        self::assertResponseStatusCodeSame(403);
    }

    public function testCommentingOnUnknownTicketReturns404(): void
    {
        $ownerToken = $this->registerAndLogin('jane@example.com');

        $this->client->request('POST', '/api/tickets/00000000-0000-7000-8000-000000000000/comments', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$ownerToken,
        ], content: json_encode(['content' => 'Hello']));

        self::assertResponseStatusCodeSame(404);
    }
}
