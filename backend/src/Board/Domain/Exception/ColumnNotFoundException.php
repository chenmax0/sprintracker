<?php

declare(strict_types=1);

namespace App\Board\Domain\Exception;

final class ColumnNotFoundException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Column not found.');
    }
}
