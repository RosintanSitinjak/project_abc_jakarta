<?php

namespace App\Enums;

enum Role: int
{
    case Owner = 1;
    case Admin = 2;
    case Writer = 3;

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Writer => 'Writer',
        };
    }
}
