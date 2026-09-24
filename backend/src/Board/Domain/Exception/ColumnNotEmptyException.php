<?php

declare(strict_types=1);

namespace App\Board\Domain\Exception;

final class ColumnNotEmptyException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This column still has tickets in it. Move them out before deleting it.');
    }
}
