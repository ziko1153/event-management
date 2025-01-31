<?php

namespace App\Controllers;

use App\Enums\EventTypeEnum;
use App\Model\Event;
use App\Service\EventSearchService;

class HomeController
{

    public Event $eventModel;
    public EventSearchService $searchService;
    public  function __construct()
    {
        $this->eventModel = new Event;
        $this->searchService = new EventSearchService();
    }
    public function home()
    {
        $query = "SELECT events.*, users.name as organizer_name, users.avatar as organizer_avatar
        FROM events
        LEFT JOIN users ON events.organizer_id = users.id
        WHERE events.is_featured = 1
        ORDER BY created_at DESC
        LIMIT 10";

        $featuredEvents = $this->eventModel->executeRawQuery($query);

        return view('home', [
            'featuredEvents' => $featuredEvents,
        ]);
    }

    public function search(array $params)
    {
        $filters = [
            'keyword' => $params['keyword'] ?? '',
            'types' => $params['types'] ?? [],
            'min_price' => $params['min_price'] ?? null,
            'max_price' => $params['max_price'] ?? null,
            'start_date' => $params['start_date'] ?? null,
            'end_date' => $params['end_date'] ?? null,
            'sort' => $params['sort'] ?? null,
            'is_featured' => $params['is_featured'] ?? 0,
            'page' => $params['page'] ?? 1,
        ];

        $searchResults = $this->searchService->search($filters);

        return view('search', [
            'events' => $searchResults['events'],
            'currentPage' => $searchResults['currentPage'],
            'totalPages' => $searchResults['totalPages'],
            'totalEvents' => $searchResults['totalEvents'],
            'types' => EventTypeEnum::getEventEnum(),
            'filters' => $filters
        ]);
    }
}