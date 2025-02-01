<?php

namespace App\Service;

use App\Enums\EventStatusEnum;
use App\Enums\RoleEnum;
use App\Model\Event;
use App\Model\EventRegistration;
use Config\Database;

class EventRegistrationService
{
    private EventRegistration $registrationModel;
    private Event $eventModel;
    private \PDO $connection;

    private array $currentEvent;

    public function __construct()
    {
        $this->registrationModel = new EventRegistration();
        $this->eventModel = new Event();
        $this->connection = Database::getInstance()->getConnection();
    }

    public function setCurrentEvent(array $event)
    {

        $this->currentEvent = $event;
    }

    public function registerForEvent(string $slug, ?string $paymentMethod  = null): array
    {
        try {
            $event = $this->eventModel->findByColumn('slug', $slug);

            $user  = $_SESSION['user'] && $_SESSION['user']['role'] ==  RoleEnum::USER->value ? $_SESSION['user'] : null;

            if (!$user) {
                return $this->sendValidationError('YOu are not authorized to this operation', 403);
            }

            if (!$event) {
                return $this->sendValidationError('Event Not Found', 404);
            }

            $this->setCurrentEvent($event);

            if ($response = $this->checkEventValidation()) {
                return $response;
            }

            $this->connection->beginTransaction();
            $registrationNumber = 'REG-' . date('Ymd') . '-' . uniqid();

            $registrationData = [
                'event_id' => $this->currentEvent['id'],
                'user_id' => $user['id'],
                'registration_number' => $registrationNumber,
                'amount' => $event['ticket_price'],
                'payment_status' => $event['ticket_price'] > 0 ? 'pending' : 'completed',
                'payment_method' => $paymentMethod
            ];

            $this->registrationModel->create($registrationData);

            $this->eventModel->update($this->currentEvent['id'], [
                'current_capacity' => $event['current_capacity'] + 1
            ]);

            $this->connection->commit();

            return [
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'registration_number' => $registrationNumber,
                    'requires_payment' => $event['ticket_price'] > 0,
                    'payment_method' => $paymentMethod
                ]
            ];
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    private function sendValidationError(string $message, int $code)
    {
        return [
            'success' => 'false',
            'validation_error' => true,
            'message' => $message,
            'code' => $code
        ];
    }

    private function checkEventValidation(): array|bool
    {
        if ($this->isUserAlreadyRegistered()) {
            return $this->sendValidationError('You are already registered for this event', 422);
        }

        if ($this->isEventCapacityFull()) {
            return $this->sendValidationError('Event is fully Booked', 422);
        }

        if ($this->isRegistrationDeadlinePassed()) {
            return $this->sendValidationError('Registration Deadline Passed', 422);
        }

        if ($this->isEventEnd()) {
            return $this->sendValidationError('Event is closed', 422);
        }

        if ($this->currentEvent['status'] != EventStatusEnum::PUBLISHED->value) {
            return $this->sendValidationError('Event is not published', 422);
        }

        return false;
    }

    public function isUserAlreadyRegistered(): bool
    {
        return $this->registrationModel->isUserRegistered($this->currentEvent['id'], $_SESSION['user']['id'] ?? SQLITE3_NULL);
    }

    public function isEventCapacityFull(): bool
    {
        return $this->currentEvent['current_capacity'] >= $this->currentEvent['max_capacity'];
    }

    public function isRegistrationDeadlinePassed(): bool
    {
        return   strtotime($this->currentEvent['registration_deadline']) < time();
    }

    public function isEventEnd(): bool
    {
        return strtotime($this->currentEvent['end_date']) <= time();
    }
}
