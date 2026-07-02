<?php

namespace App\Enums;

enum Role: int
{
    case Owner = 1;
    case Admin = 2;
    case Pelanggan = 3;

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pimpinan ABC Jakarta',
            self::Admin => 'Admin',
            self::Pelanggan => 'Pelanggan/Gereja',
        };
    }
}
