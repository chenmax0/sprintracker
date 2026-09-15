<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Symfony\Controllers;

use App\Demo\Application\GetDemoSnapshot\GetDemoSnapshotHandler;
use App\Demo\Application\GetDemoSnapshot\GetDemoSnapshotPayload;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DemoController
{
    public function show(GetDemoSnapshotHandler $handler): JsonResponse
    {
        $snapshot = $handler->handle(new GetDemoSnapshotPayload());

        return new JsonResponse([
            'team' => null !== $snapshot['team'] ? [
                'id' => $snapshot['team']['id'],
                'name' => $snapshot['team']['name'],
            ] : null,
            'project' => null !== $snapshot['project'] ? [
                'id' => $snapshot['project']['id'],
                'teamId' => $snapshot['project']['team_id'],
                'name' => $snapshot['project']['name'],
            ] : null,
            'members' => array_map(
                static fn (array $member) => [
                    'memberId' => $member['member_id'],
                    'role' => $member['role'],
                    'email' => $member['email'],
                    'name' => $member['name'],
                ],
                $snapshot['members'],
            ),
            'sprints' => array_map(
                static fn (array $sprint) => [
                    'id' => $sprint['id'],
                    'projectId' => $sprint['project_id'],
                    'name' => sprintf('Sprint %d', $sprint['number']),
                    'startDate' => $sprint['start_date'],
                    'endDate' => $sprint['end_date'],
                ],
                $snapshot['sprints'],
            ),
            'tickets' => array_map(
                static fn (array $ticket) => [
                    'id' => $ticket['id'],
                    'projectId' => $ticket['project_id'],
                    'sprintId' => $ticket['sprint_id'],
                    'title' => $ticket['title'],
                    'description' => $ticket['description'],
                    'status' => $ticket['status'],
                    'reporterId' => $ticket['reporter_id'],
                    'assigneeId' => $ticket['assignee_id'],
                ],
                $snapshot['tickets'],
            ),
        ]);
    }
}
