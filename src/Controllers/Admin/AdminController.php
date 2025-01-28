<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Model\User;
use App\Model\Event;
use App\Service\ValidationService;

class AdminController extends BaseController
{
    private User $userModel;
    private Event $eventModel;
    private ValidationService $validator;

    public function __construct()
    {
        $this->userModel = new User();
        $this->eventModel = new Event();
        $this->validator = new ValidationService();
    }

    public function dashboard(): void
    {
        $stats = [
            'total_users' => $this->userModel->count(),
            'total_events' => $this->eventModel->count(),
            'recent_events' => $this->eventModel->findWithQuery(
                ['events.*', 'users.name as organizer_name'],
                [],
                [['type' => 'LEFT', 'table' => 'users', 'on' => 'events.created_by = users.id']],
                ['events.created_at' => 'DESC'],
                '',
                5
            )
        ];

        view('admin/dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats
        ], 'admin');
    }
}
