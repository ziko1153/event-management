<?php

namespace App\Service;

use App\Model\Event;
use App\Model\User;
use Config\Database;
use App\Model\Organization;

class EventService
{
    private Event $eventModel;
    private ImageService $imageService;
    private Database $db;
    private \PDO $connection;
    private Organization $organizationModel;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->imageService = new ImageService(__DIR__ . '/../../public/img/events/');
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
        $this->organizationModel = new Organization();
    }

    public function getEventsList(array $filters, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $whereConditions = ['1=1'];
        $params = [];

        if (isUserOrganizer()) {
            $whereConditions[] = "events.organizer_id = :org_id";
            $params[':org_id'] = $_SESSION['user']['organizer']['id'];
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "events.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereConditions[] = "events.event_type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            if (strlen($search) < 11) {
                $whereConditions[] = "(events.title LIKE :title_search OR events.description LIKE :des_search)";
                $params[':title_search'] = "%{$search}%";
                $params[':des_search'] = "%{$search}%";
            } else {
                $whereConditions[] = "MATCH(events.title, events.description) AGAINST (:search IN NATURAL LANGUAGE MODE)";
                $params[':search'] = $search;
            }
        }


        if (!empty($filters['date_type']) && !empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateColumn = match ($filters['date_type']) {
                'registration' => 'registration_deadline',
                'start' => 'start_date',
                'end' => 'end_date',
                default => 'start_date'
            };

            $whereConditions[] = "events.{$dateColumn} BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['organizer_id'])) {
            $whereConditions[] = "events.organizer_id = :organizer_id";
            $params[':organizer_id'] = $filters['organizer_id'];
        }


        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'DESC';

        $allowedSortFields = ['registration_deadline', 'start_date', 'end_date', 'title'];
        $sort = in_array($sort, $allowedSortFields) ? $sort : 'created_at';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT 
                    events.*,
                    organizations.name as organizer_name,
                    COALESCE(attendees.attendee_count, 0) as attendee_count
                 FROM events
                 LEFT JOIN organizations ON events.organizer_id = organizations.id
                 LEFT JOIN (
                    SELECT event_id, COUNT(*) as attendee_count 
                    FROM event_registrations 
                    GROUP BY event_id
                 ) attendees ON events.id = attendees.event_id
                 WHERE " . implode(' AND ', $whereConditions) . "
                 ORDER BY events.$sort $order
                 LIMIT :limit OFFSET :offset";

        // dd($query);

        $countQuery = "SELECT COUNT(*) as total 
                 FROM events
                 WHERE " . implode(' AND ', $whereConditions);

        $totalCount = $this->eventModel->executeRawQuery($countQuery, $params)[0]['total'] ?? 0;

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $events = $this->eventModel->executeRawQuery($query, $params);


        return [
            'events' => $events,
            'total' => (int) $totalCount,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }

    public function createEvent(array $data, array $file): array
    {
        try {
            $this->connection->beginTransaction();

            $data['thumbnail'] = $this->imageService->uploadImage($file);
            $data['slug'] = $this->generateSlug($data['title']);
            $data['created_by'] = $_SESSION['user']['id'];
            if (isUserOrganizer()) {
                $data['organizer_id'] = $_SESSION['user']['organizer']['id'];
                $data['is_featured'] = 0; // only admin can do this,no other user
            }

            $eventId = $this->eventModel->create($data);

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Event created successfully',
                'event_id' => $eventId
            ];
        } catch (\Exception $e) {
            $this->connection->rollBack();

            if (isset($data['thumbnail'])) {
                $this->imageService->deleteImage($data['thumbnail']);
            }

            return [
                'success' => false,
                'error' => 'Failed to create event: ' . $e->getMessage(),
                'old' => $data
            ];
        }
    }

    public function updateEvent(array $data, ?array $file = null): array
    {

        try {
            $this->connection->beginTransaction();
            $event = $this->eventModel->findByColumn('slug', $data['slug']);
            
            if (!$event) {
                throw new \Exception('Event not found');
            }

            if (isUserOrganizer() &&  !$this->canOrganizerPerformThisTask($event['organizer_id'])) {
                throw new \Exception('Not Authorized');
            }

            if ($event['current_capacity'] > $data['max_capacity']) {
                return [
                    'validation_error' => true,
                    'success' => false,
                    'errors' => ['max_capacity' => 'Event is Full, write same or greater than Current Attendees: ' . $event['current_capacity']],
                ];
            }

            if ($file && !empty($file['name'])) {
                $oldThumbnail = $event['thumbnail'];
                $data['thumbnail'] = $this->imageService->uploadImage($file);

                if ($oldThumbnail && !in_array(basename($oldThumbnail), ['event1.png', 'event2.png'])) {
                    $this->imageService->deleteImage($oldThumbnail);
                }
            }


            if ($data['title'] !== $event['title']) {
                $data['slug'] = $this->generateSlug($data['title']);
            }


            $data['ticket_price'] = empty($data['ticket_price']) ? 0 : $data['ticket_price'];
            $data['max_capacity'] = empty($data['max_capacity']) ? 0 : $data['max_capacity'];

            if (isUserOrganizer()) {
                $data['is_featured'] = 0; // Only admin can edit this feature
            }

            // Update event
            $this->eventModel->update($event['id'], $data);

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Event updated successfully'
            ];
        } catch (\Exception $e) {
            $this->connection->rollBack();

            if (isset($data['thumbnail']) && $data['thumbnail'] !== $event['thumbnail']) {
                $this->imageService->deleteImage($data['thumbnail']);
            }

            return [
                'success' => false,
                'error' => 'Failed to update event: ' . $e->getMessage(),
                'old' => $data
            ];
        }
    }

    public function deleteEvent(string $slug): array
    {
        try {
            $this->connection->beginTransaction();

            $event = $this->eventModel->findByColumn('slug', $slug);
            if (!$event) {
                throw new \Exception('Event not found');
            }

            if (isUserOrganizer() && !$this->canOrganizerPerformThisTask($event['organizer_id'])) {
                throw new \Exception('Not Authorized to perform this task');
            }

            $this->eventModel->delete($event['id']);

            if ($event['thumbnail'] && !in_array(basename($event['thumbnail']), ['event1.png', 'event2.png'])) {
                $this->imageService->deleteImage($event['thumbnail']);
            }

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Event deleted successfully'
            ];
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ];
        }
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;

        if ($this->eventModel->findByColumn('slug', $slug)) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function searchOrganizers(string $search): array
    {
        if (strlen($search) < 3) {
            return ['success' => false, 'message' => 'Please enter at least 3 characters'];
        }

        try {
            $query = "SELECT organizations.id, organizations.name,users.email FROM organizations
            join users on users.id  = organizations.user_id
             WHERE organizations.name LIKE :search LIMIT 10";
            $params = [':search' => "%{$search}%"];

            $organizers = $this->organizationModel->executeRawQuery($query, $params);

            return [
                'success' => true,
                'organizers' => $organizers
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'Failed to fetch organizers'
            ];
        }
    }


    public function canOrganizerPerformThisTask(int $organizerId): bool
    {
        return isUserOrganizer() &&  $organizerId == $_SESSION['user']['organizer']['id'];
    }
}