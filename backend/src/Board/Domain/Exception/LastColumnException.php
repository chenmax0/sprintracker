<?php

declare(strict_types=1);

namespace App\Board\Domain\Exception;

final class LastColumnException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('A project must always have at least one column.');
    }
}
