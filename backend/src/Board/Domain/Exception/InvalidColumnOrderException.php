<?php

declare(strict_types=1);

namespace App\Board\Domain\Exception;

final class InvalidColumnOrderException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The given column order must contain exactly the project\'s current columns, once each.');
    }
}
