<?php

namespace App\Model;

use App\Traits\DatabaseTrait;

class EventRegistration extends BaseModel
{
    protected string $table = 'event_registrations';

    public function getUserRegistrations(int $userId): array
    {
        $query = "SELECT 
            er.*, 
            e.title, 
            e.start_date,
            e.end_date,
            e.venue_details,
            e.location,
            e.thumbnail
        FROM event_registrations er
        LEFT JOIN events e ON er.event_id = e.id
        WHERE er.user_id = :user_id
        ORDER BY er.registered_at DESC";

        return $this->executeRawQuery($query, [':user_id' => $userId]);
    }

    public function isUserRegistered(int $eventId, ?int $userId = null): bool
    {
        if (!$userId) return false;

        return (bool) $this->findAll([
            'event_id' => $eventId,
            'user_id' => $userId
        ]);
    }
}
