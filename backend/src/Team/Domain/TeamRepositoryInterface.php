<?php

declare(strict_types=1);

namespace App\Team\Domain;

interface TeamRepositoryInterface
{
    public function findById(TeamId $id): ?Team;

    public function save(Team $team): void;
}
