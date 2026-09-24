<?php

declare(strict_types=1);

namespace App\Board\Domain\Exception;

final class NotAProjectMemberException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('You are not a member of this project.');
    }
}
