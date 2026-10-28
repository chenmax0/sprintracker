<?php

declare(strict_types=1);

namespace App\Sprint\Domain\Exception;

final class SprintAlreadyCompletedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This sprint is already completed.');
    }
}
