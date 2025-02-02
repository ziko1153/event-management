<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Enums\EventStatusEnum;
use App\Enums\EventTypeEnum;
use App\Model\Event;
use App\Model\EventRegistration;
use App\Service\EventRegistrationService;

class UserEventController extends BaseController
{
    private Event $eventModel;
    private EventRegistrationService $registrationService;
    private EventRegistration $eventRegistration;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->eventRegistration = new EventRegistration();
        $this->registrationService = new EventRegistrationService();
    }

    public function myEvents(array $params): void
    {
        $page = $params['page'] ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $totalCount = $this->eventRegistration->count([
            'user_id' => $_SESSION['user']['id']
        ]);

        $query = "SELECT er.*, e.*, org.name as organizer_name 
            FROM event_registrations er
            JOIN events e ON er.event_id = e.id
            LEFT JOIN organizations org ON e.organizer_id = org.id
            WHERE er.user_id = :user_id
            ORDER BY er.registered_at DESC
            LIMIT :limit OFFSET :offset";

        $registrations = $this->eventRegistration->executeRawQuery($query, [
            ':user_id' => $_SESSION['user']['id'],
            ':offset' => $offset,
            ':limit' => $perPage
        ]);

        $totalPages = ceil($totalCount / $perPage);

        view('user/events/my-events', [
            'registrations' => [
                'data' => $registrations,
                'current_page' => (int) $page,
                'total_pages' => $totalPages,
                'per_page' => $perPage
            ]
        ]);
    }

    public function showRegistrationForm(array $params): void
    {

        $query = "SELECT events.*, organizations.name as organizer_name, organizations.logo as organizer_logo
        FROM events
        LEFT JOIN organizations ON events.organizer_id = organizations.id
        WHERE events.slug = :slug
        AND events.status = :status
        ";

        $queryParam = [];
        $queryParam[':slug'] = $params['slug'];
        $queryParam[':status'] = EventStatusEnum::PUBLISHED->value;



        $event = $this->eventModel->executeRawQuery($query, $queryParam);

        if (!$event) {
            $_SESSION['error'] = 'Event not found';
            header('Location: /');
            exit;
        }

        $this->registrationService->setCurrentEvent($event[0]);

        $isRegistered = $this->registrationService->isUserAlreadyRegistered();
        $isCapacityFull = $this->registrationService->isEventCapacityFull();
        $isDeadlinePassed = $this->registrationService->isRegistrationDeadlinePassed();

        $isEventEnd = $this->registrationService->isEventEnd();


        view('events/register', [
            'event' => $event[0],
            'isRegistered' => $isRegistered,
            'isCapacityFull' => $isCapacityFull,
            'isDeadlinePassed' => $isDeadlinePassed,
            'isEventEnd' => $isEventEnd
        ]);
    }
    public function register(array $params): void
    {

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Please login to register for events';
            header('Location: /login');
            exit;
        }


        $result = $this->registrationService->registerForEvent(
            $params['slug'],
            $params['payment_method'] ?? null
        );

        if ($result['success']) {
            if ($result['data']['requires_payment']) {
                header('Location: /events/payment/process/' . $result['data']['registration_number']);
            } else {
                $_SESSION['success'] = $result['message'];
                header('Location: /user/my-events');
            }
        } else {
            if (isset($result['validation_error'])) {
                $_SESSION['error'] = $result['message'];
            } else {
             
                $_SESSION['error'] = 'Something went wrong on server,please try again later';
            }

            header('Location: /events/register' . $params['slug']);
        }
        exit;
    }


    public function paymentProcess(array $params): void
    {

        $registration = $this->eventRegistration->findWithQuery(conditions: [
            'registration_number' => $params['registration_number'],
            'payment_status' => 'pending',
            'user_id' => $_SESSION['user']['id']
        ]);

        if (!isset($registration[0])) {
            $_SESSION['error'] = 'Failed to process payment';
            header('Location: /');
            exit;
        }

        view('events/payment-process', [
            'registration' => $registration[0]
        ]);
    }
    public function paymentComplete(array $params): void
    {
        $registration = $this->eventRegistration->findWithQuery(conditions: [
            'registration_number' => $params['registration_number'],
            'payment_status' => 'pending',
            'user_id' => $_SESSION['user']['id']
        ]);

        if (!isset($registration[0])) {
            $_SESSION['error'] = 'Invalid registration';
            header('Location: /');
            exit;
        }

        $status = $params['status'] ?? 'failed';
        $success = $status === 'success';

        // Update payment status
        $this->eventRegistration->update($registration[0]['id'], [
            'payment_status' => $success ? 'completed' : 'failed',
            'payment_details' => json_encode([
                'payment_created_at' => date('Y-m-d H:i:s'),
                'payment_id' => uniqid('pay'),
            ]),
        ]);

        if ($success) {
            $_SESSION['success'] = 'Payment completed successfully!';
            header('Location: /user/my-events');
        } else {
            $_SESSION['error'] = 'Payment failed. Please try again.';
            header('Location: /user/my-events/');
        }

        exit;
    }
    public function show(array $params): void
    {
        $query = "SELECT er.*, e.*, org.name as organizer_name,  org.logo as organizer_logo
            FROM event_registrations er
            JOIN events e ON er.event_id = e.id
            LEFT JOIN  organizations org ON e.organizer_id = org.id
            WHERE e.slug = :slug AND er.user_id = :user_id";

        $result = $this->eventModel->executeRawQuery($query, [
            ':slug' => $params['slug'],
            ':user_id' => $_SESSION['user']['id']
        ]);

        if (!$result) {
            $_SESSION['error'] = 'Event registration not found';
            header('Location: /user/my-events');
            exit;
        }

        view('user/events/show', [
            'registration' => $result[0]
        ]);
    }
}