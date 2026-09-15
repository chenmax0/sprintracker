<?php

declare(strict_types=1);

namespace App\Sprint\Domain\Exception;

final class SprintAlreadyActiveException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This project already has an active sprint.');
    }
}
