<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case ORGANIZER = 'organizer';
    case USER = 'user';

    public static function getRoleEnum(): array
    {
        return array_column(RoleEnum::cases(), 'value');
    }
}
