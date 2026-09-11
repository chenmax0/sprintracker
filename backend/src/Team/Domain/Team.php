<?php

declare(strict_types=1);

namespace App\Team\Domain;

final class Team
{
    private function __construct(
        private TeamId $id,
        private string $name,
    ) {
    }

    public static function create(TeamId $id, string $name): self
    {
        return new self($id, $name);
    }

    /**
     * Reconstitutes a Team from persisted data. Only the persistence layer should call this.
     */
    public static function fromPersistence(TeamId $id, string $name): self
    {
        return new self($id, $name);
    }

    public function getId(): TeamId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
