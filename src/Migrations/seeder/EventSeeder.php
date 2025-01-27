<?php

namespace App\Migrations\Seeder;

use App\Enums\EventStatusEnum;
use App\Enums\EventTypeEnum;
use App\Model\Event;
use App\Model\EventCategory;

class EventSeeder
{
    public function run(): void
    {
        $eventModel = new Event();
        $events = [
            [
                'created_by' => 3,
                'title' => 'Tech Conference 2025',
                'thumbnail' => '/events/event1.png',
                'slug' => 'tech-conference-2025',
                'description' => 'An amazing tech conference.',
                'location' => 'San Francisco, CA',
                'venue_details' => 'Moscone Center, Hall A',
                'start_date' => '2025-09-15 09:00:00',
                'end_date' => '2025-09-17 17:00:00',
                'registration_deadline' => '2025-09-10 23:59:59',
                'max_capacity' => 500,
                'current_capacity' => 250,
                'ticket_price' => 199.99,
                'event_type' => EventTypeEnum::CONFERENCE->value,
                'status' => EventStatusEnum::PUBLISHED->value,
                'is_featured' => true,
                'is_private' => false,
            ],
            [
                'created_by' => 4,
                'title' => 'Music Festival 2025',
                'thumbnail' => '/events/event2.png',
                'slug' => 'music-festival-2025',
                'description' => 'The best music festival of the year.',
                'location' => 'Austin, TX',
                'venue_details' => 'Zilker Park',
                'start_date' => '2025-10-10 10:00:00',
                'end_date' => '2025-10-12 23:59:59',
                'registration_deadline' => '2025-10-05 23:59:59',
                'max_capacity' => 1000,
                'current_capacity' => 500,
                'ticket_price' => 299.99,
                'event_type' => EventTypeEnum::OTHER->value,
                'status' => EventStatusEnum::PUBLISHED->value,
                'is_featured' => false,
                'is_private' => false,
            ],
        ];

        $eventModel->bulkInsert($events);

        echo count($events) . " events seeded successfully.\n";

        $this->eventCategories();
    }

    protected function eventCategories()
    {
        $eventCategoryModel = new EventCategory();

        $categories = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Events focused on technology and innovation.',
            ],
            [
                'name' => 'Music',
                'slug' => 'music',
                'description' => 'Events focused on music and live performances.',
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Events focused on sports and physical activities.',
            ],
        ];

        $eventCategoryModel->bulkInsert($categories);

        echo count($categories) . " event categories seeded successfully.\n";
    }
}
