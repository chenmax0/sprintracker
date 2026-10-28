<?php

declare(strict_types=1);

namespace App\Project\Application\Port;

/**
 * Anti-corruption layer towards the Board context: every new project needs
 * a starting set of kanban columns, but Project never depends on Board's
 * Domain classes - this seeds them with a direct SQL insert instead.
 */
interface DefaultBoardColumnsSeederInterface
{
    public function seedDefaults(string $projectId): void;
}
