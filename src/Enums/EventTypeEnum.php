<?php

namespace App\Enums;

enum EventTypeEnum: string
{
    case CONFERENCE = 'conference';
    case WORKSHOP = 'workshop';
    case SEMINAR = 'seminar';
    case CONCERT = 'concert';
    case EXHIBITION = 'exhibition';
    case OTHER = 'other';

    public static function getEventEnum(): array
    {
        return array_column(EventTypeEnum::cases(), 'value');
    }

    public static function getTypeColor(string $type): string
    {
        return match (strtolower($type)) {
            self::CONFERENCE->value => 'primary',
            self::WORKSHOP->value => 'success',
            self::SEMINAR->value => 'info',
            self::CONCERT->value => 'danger',
            self::EXHIBITION->value => 'warning',
            self::OTHER->value => 'secondary',
            default => 'secondary'
        };
    }
}