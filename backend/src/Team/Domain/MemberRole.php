<?php

declare(strict_types=1);

namespace App\Team\Domain;

enum MemberRole: string
{
    case Owner = 'owner';
    case Member = 'member';
}
