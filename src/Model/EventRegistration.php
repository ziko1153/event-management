<?php

namespace App\Model;

use App\Traits\DatabaseTrait;

class EventRegistration extends BaseModel
{
    protected string $table = 'event_registrations';


    public function isUserRegistered(int $eventId, ?int $userId = null): bool
    {
        if (!$userId) return false;

        return (bool) $this->findAll([
            'event_id' => $eventId,
            'user_id' => $userId
        ]);
    }
}