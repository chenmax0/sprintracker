<?php

declare(strict_types=1);

namespace App\Sprint\Domain\Exception;

final class InvalidSprintDatesException extends \DomainException
{
    public function __construct(string $reason)
    {
        parent::__construct($reason);
    }
}
