<?php

namespace App\Migrations\Seeder;

use App\Enums\EventStatusEnum;
use App\Enums\EventTypeEnum;
use App\Model\Event;
use App\Model\EventCategory;
use DateTime;

class EventSeeder
{
    private array $eventTitles = [
        'Tech Conference',
        'Music Festival',
        'Sports Tournament',
        'Art Exhibition',
        'Food Festival',
        'Business Summit',
        'Gaming Convention',
        'Science Fair',
        'Film Festival',
        'Cultural Festival',
        'Book Fair',
        'Career Fair'
    ];

    private array $cities = [
        'Dhaka',
        'Chittagong',
        'Sylhet',
        'Rajshahi',
        'Khulna',
        'Barisal',
        'Rangpur',
        "Cox\'s Bazar",
        'Mymensingh',
        'Comilla'
    ];

    private array $venues = [
        'International Convention City Bashundhara',
        'Bangabandhu International Conference Center',
        'Bangladesh Army Stadium',
        'Sher-e-Bangla National Cricket Stadium',
        'National Parliament House',
        'Bangladesh China Friendship Conference Center',
        'Sheikh Kamal International Cricket Stadium',
        'Sylhet International Cricket Stadium',
        'MA Aziz Stadium',
        'Shaheed Suhrawardy Indoor Stadium'
    ];

    public function run(int $count = 100): void
    {
        $eventModel = new Event();
        $events = [];
        $startDate = new DateTime();
        $startDate->modify('+1 month');
        $eventTypes = EventTypeEnum::getEventEnum();

        for ($i = 1; $i <= $count; $i++) {
            $title = $this->eventTitles[array_rand($this->eventTitles)] . ' ' . date('Y');
            $eventType = array_rand(EventTypeEnum::getEventEnum());
            $startDate->modify('+' . rand(1, 7) . ' days');
            $endDate = clone $startDate;
            $endDate->modify('+' . rand(1, 3) . ' days');
            $regDeadline = clone $startDate;
            $regDeadline->modify('-' . rand(2, 10) . ' days');

            $maxCapacity = rand(100, 1000);
            $currentCapacity = rand(0, $maxCapacity);
            $eventType = $eventTypes[array_rand($eventTypes)];

            $events[] = [
                'created_by' => rand(3, 4),
                'organizer_id' => rand(3, 4),
                'title' => $title . ' #' . $i,
                'thumbnail' => '/events/event' . rand(1, 2) . '.png',
                'slug' => strtolower(str_replace(' ', '-', $title)) . '-' . $i,
                'description' => 'Join us for the exciting ' . strtolower($title) . ' featuring the best of Bangladesh.',
                'location' => $this->cities[array_rand($this->cities)],
                'venue_details' => $this->venues[array_rand($this->venues)],
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'registration_deadline' => $regDeadline->format('Y-m-d H:i:s'),
                'max_capacity' => $maxCapacity,
                'current_capacity' => $currentCapacity,
                'ticket_price' => rand(500, 5000) . '.00',
                'event_type' => $eventType,
                'status' => EventStatusEnum::PUBLISHED->value,
                'is_featured' => (rand(1, 10) > 8),
            ];
        }

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