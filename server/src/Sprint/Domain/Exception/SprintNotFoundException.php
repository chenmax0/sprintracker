<?php

declare(strict_types=1);

namespace App\Sprint\Domain\Exception;

final class SprintNotFoundException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Sprint not found.');
    }
}
