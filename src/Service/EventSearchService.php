<?php

namespace App\Service;

use App\Model\Event;
use Config\Database;

class EventSearchService
{
    private Event $eventModel;

    public function __construct()
    {
        $this->eventModel = new Event();
    }

    public function search(array $filters): array
    {
        $page = $filters['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $whereConditions = ['events.status = :status'];
        $params = [':status' => 'published'];

        // Search keyword
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            if (strlen($keyword) >= 3) {
                $whereConditions[] = "MATCH(events.title, events.description) AGAINST(:search IN NATURAL LANGUAGE MODE)";
                $params[':search'] = $keyword;
            } else {
                $whereConditions[] = "(events.title LIKE :search OR events.description LIKE :search)";
                $params[':search'] = '%' . $keyword . '%';
            }
        }
        if (isset(($filters['is_featured'])) && $filters['is_featured'] == 1) {
            $whereConditions[] = 'events.is_featured = 1';
        }

        // Filter by type
        if (!empty($filters['types'])) {
            $types = is_array($filters['types']) ? $filters['types'] : [$filters['types']];
            $typeParams = [];
            foreach ($types as $i => $type) {
                $param = ":type_$i";
                $typeParams[] = $param;
                $params[$param] = $type;
            }
            $whereConditions[] = "events.event_type IN (" . implode(',', $typeParams) . ")";
        }

        // Filter by price range
        if (isset($filters['min_price']) && (int)$filters['min_price'] >= 0) {
            $whereConditions[] = "events.ticket_price >= :min_price";
            $params[':min_price'] = floatval($filters['min_price']);
        }
        if (isset($filters['max_price']) && (int)$filters['max_price'] >= 0) {
            $whereConditions[] = "events.ticket_price <= :max_price";
            $params[':max_price'] = floatval($filters['max_price']);
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $whereConditions[] = "events.start_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereConditions[] = "events.end_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        // Sort options
        $sortField = 'events.created_at';
        $sortOrder = 'DESC';

        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'date_asc':
                    $sortField = 'events.start_date';
                    $sortOrder = 'ASC';
                    break;
                case 'date_desc':
                    $sortField = 'events.start_date';
                    $sortOrder = 'DESC';
                    break;
                case 'price_asc':
                    $sortField = 'events.ticket_price';
                    $sortOrder = 'ASC';
                    break;
                case 'price_desc':
                    $sortField = 'events.ticket_price';
                    $sortOrder = 'DESC';
                    break;
            }
        }

        $query = "SELECT events.*, users.name as organizer_name, users.avatar as organizer_avatar
                FROM events
                LEFT JOIN users ON events.organizer_id = users.id
                WHERE " . implode(' AND ', $whereConditions) . "
                ORDER BY {$sortField} {$sortOrder}
                LIMIT :limit OFFSET :offset";

        $countQuery = "SELECT COUNT(*) as total 
                      FROM events 
                      WHERE " . implode(' AND ', $whereConditions);

        $totalEvents = $this->eventModel->executeRawQuery($countQuery, $params)[0]['total'] ?? 0;

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $events = $this->eventModel->executeRawQuery($query, $params);

        return [
            'events' => $events,
            'currentPage' => (int) $page,
            'totalPages' => ceil($totalEvents / $limit),
            'totalEvents' => (int) $totalEvents
        ];
    }
}