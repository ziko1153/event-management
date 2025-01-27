<?php

namespace App\Enums;

enum EventStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public static function getEventStatusEnum(): array
    {
        return array_column(EventStatusEnum::cases(), 'value');
    }
}
