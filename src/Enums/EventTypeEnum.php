<?php

namespace App\Enums;

enum EventTypeEnum: string
{
    case CONFERENCE = 'conference';
    case WORKSHOP = 'workshop';
    case SEMINAR = 'seminar';
    case OTHER = 'other';

    public static function getEventEnum(): array
    {
        return array_column(EventTypeEnum::cases(), 'value');
    }
}
