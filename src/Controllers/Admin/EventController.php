<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Model\User;
use App\Model\Event;
use App\Enums\EventTypeEnum;
use App\Service\EventService;
use App\Enums\EventStatusEnum;
use App\Model\Organization;
use App\Service\ValidationService;

class EventController extends BaseController
{
    private Event $eventModel;
    private User $userModel;
    private Organization $organizerModel;
    private ValidationService $validator;
    private EventService $eventService;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->userModel = new User();
        $this->organizerModel = new Organization();
        $this->validator = new ValidationService();
        $this->eventService = new EventService();
    }

    public function getEvents(array $params): void
    {
        $page = $params['page'] ?? 1;
        $filters = [
            'search' => $params['search'] ?? '',
            'status' => $params['status'] ?? '',
            'type' => $params['type'] ?? '',
            'date_type' => $params['date_type'] ?? '',
            'organizer_id' => $params['organizer_id'] ?? '',
            'start_date' => $params['start_date'] ?? '',
            'end_date' => $params['end_date'] ?? '',
            'sort' => $params['sort'] ?? 'created_at',
            'order' => $params['order'] ?? 'DESC'
        ];;

        if ($this->isAjaxRequest()) {
            $result = $this->eventService->getEventsList($filters, (int) $page);
            $this->jsonResponse([
                'success' => true,
                'events' => $result['events'],
                'pagination' => [
                    'current' => (int) $page,
                    'total' => $result['total_pages']
                ]
            ]);
        }

        view('admin/events/index', [
            'title' => 'Events Management',
            'statuses' => EventStatusEnum::getEventStatusEnum(),
            'types' => EventTypeEnum::getEventEnum(),
            'filters' => $filters,
        ], 'admin');
    }

    public function searchOrganizers(): void
    {
        $search = $_GET['search'] ?? '';
        $this->jsonResponse($this->eventService->searchOrganizers($search));
    }


    public function create(): void
    {
        if (isUserOrganizer()) {
            $organizers = [$this->organizerModel->findByColumn('user_id', $_SESSION['user']['id'])];
            $_SESSION['old']['organizer_id'] = $_SESSION['user']['organizer']['id'];
        } else {
            $organizers = $this->organizerModel->findAll();
        }

        view('admin/events/create', [
            'title' => 'Create Event',
            'organizers' => $organizers,
            'types' => EventTypeEnum::getEventEnum(),
            'statuses' => EventStatusEnum::getEventStatusEnum()
        ], 'admin');
    }

    public function store(array $params): array
    {
        $rules = [
            'title' => ['required', 'min:3'],
            'organizer_id' => ['required', 'exists:organizations,id'],
            'description' => ['required'],
            'start_date' => ['required', 'date', 'after:registration_deadline'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'registration_deadline' => ['required', 'date'],
            'event_type' => ['required', 'enum:' . implode(',', EventTypeEnum::getEventEnum())],
            'max_capacity' => ['required', 'numeric'],
            'thumbnail' => ['image', 'type:jpg,jpeg,png', 'size:1mb'],
        ];

        if (!$this->validator->validate($params, $rules)) {
            $this->sendValidationError($this->validator->getErrors(), $params);
        }

        $result = $this->eventService->createEvent($params, $_FILES['thumbnail']);

        if (!$result['success']) {
            $this->sendError('something went wrong on server, please try again');
        }

        $this->sendSuccess($result['message'], '/admin/events');
        exit;
    }

    public function edit(array $params): void
    {
        $event = $this->eventModel->findByColumn('slug', $params['slug']);

        if (!$event) {
            $_SESSION['error'] = 'Event not found';
            header('Location: /admin/events');
            exit;
        }

        if (isUserOrganizer()) {
            $organizers = [$this->organizerModel->findByColumn('user_id', $_SESSION['user']['id'])];
        } else {
            $organizers = $this->organizerModel->findAll();
        }

        // dd($organizers);

        view('admin/events/edit', [
            'title' => 'Edit Event',
            'event' => $event,
            'organizers' => $organizers,
            'types' => EventTypeEnum::getEventEnum(),
            'statuses' => EventStatusEnum::getEventStatusEnum()
        ], 'admin');
    }

    public function update(array $params): array
    {

        $rules = [
            'title' => ['required', 'min:3'],
            'organizer_id' => ['required', 'exists:organizations,id'],
            'description' => ['required'],
            'start_date' => ['required', 'date', 'after:registration_deadline'],
            'ticket_price' => ['nullable', 'numeric'],
            'end_date' => ['required', 'date', 'after_equal:start_date'],
            'registration_deadline' => ['required', 'date'],
            'event_type' => ['required', 'enum:' . implode(',', EventTypeEnum::getEventEnum())],
            'status' => ['required', 'enum:' . implode(',', EventStatusEnum::getEventStatusEnum())],
            'max_capacity' => ['required', 'numeric']
        ];

        if (!empty($_FILES['thumbnail']['name'])) {
            $rules['thumbnail'] = ['image', 'type:jpg,jpeg,png', 'size:1mb'];
        }

        if (!$this->validator->validate($params, $rules)) {
            $this->sendValidationError($this->validator->getErrors(), $params);
        }

        $result = $this->eventService->updateEvent($params, $_FILES['thumbnail'] ?? null);
        // dd($result);
        if (!$result['success']) {

            if (isset($result['validation_error'])) {

                // dd($result);
                $this->sendValidationError($result['errors']);
            }

            $this->sendError('something went wrong on server, please try again');
        }

        $_SESSION['success'] = $result['message'];

        $this->sendSuccess($result['message'], '/admin/events');
        exit;
    }

    public function attendees(array $params): void
    {
        $event = $this->eventModel->findByColumn('slug', $params['slug']);
        if (!$event) {
            $_SESSION['error'] = 'Event not found';
            header('Location: /admin/events');
            exit;
        }


        $query = "SELECT users.*, event_registrations.*
                 FROM users 
                 INNER JOIN event_registrations ON users.id = event_registrations.user_id 
                 WHERE event_registrations.event_id = :event_id";

        $attendees = $this->eventModel->executeRawQuery($query, [':event_id' => $event['id']]);

        view('admin/events/attendees', [
            'title' => 'Event Attendees',
            'event' => $event,
            'attendees' => $attendees
        ], 'admin');
    }

    public function downloadAttendees(array $params): void
    {
        
        $event = $this->eventModel->findByColumn('slug', $params['slug']);
        if (!$event) {
            $_SESSION['error'] = 'Event not found';
            header('Location: /admin/events');
            exit;
        }

        if (isUserOrganizer() && !$this->eventService->canOrganizerPerformThisTask($event['organizer_id'])) {
            $_SESSION['error'] = 'Yuu are not authorized to perform this task';
            header('Location: /admin/events');
            exit;
        }

        $query = "SELECT users.name,users.email,users.phone,users.address,er.registered_at,er.payment_status, er.amount, er.payment_method
                 FROM users 
                 INNER JOIN event_registrations as er ON users.id = er.user_id 
                 WHERE er.event_id = :event_id";

        $attendees = $this->eventModel->executeRawQuery($query, [':event_id' => $event['id']]);

        if (!count($attendees)) {
            $this->sendError('No Attendee Found');
            exit;
        }

        $filename = "attendees-{$event['slug']}-" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Phone', 'Address', 'Registration Date', 'Payment Status', 'Amount', 'Payment Method']);

        foreach ($attendees as $attendee) {
            fputcsv($output, [
                $attendee['name'],
                $attendee['email'],
                $attendee['phone'],
                $attendee['address'],
                date('Y-m-d H:i:s', strtotime($attendee['registered_at'])),
                $attendee['payment_status'],
                $attendee['amount'],
                $attendee['payment_method']
            ]);
        }

        fclose($output);
        exit;
    }

    public function delete(array $params): array
    {

        $result = $this->eventService->deleteEvent($params['slug']);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'message' => $result['message']]);
            exit;
        }

        $this->sendSuccess($result['message'], '/admin/events');
        exit;
    }
}