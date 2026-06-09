<?php

namespace App\Enums;

enum ContactStatus: int
{
    case Open = 1;
    case Closed = 2;

    public function label(): string
    {
        return match($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }
}
