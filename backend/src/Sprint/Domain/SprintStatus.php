<?php

declare(strict_types=1);

namespace App\Sprint\Domain;

enum SprintStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
}
